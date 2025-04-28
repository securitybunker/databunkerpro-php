<?php

namespace DatabunkerPro;

use Exception;

class DatabunkerproApi {
    private $baseURL;
    private $xBunkerToken;
    private $xBunkerTenant;

    public function __construct($baseURL, $xBunkerToken = '', $xBunkerTenant = '') {
        $this->baseURL = $baseURL;
        $this->xBunkerToken = $xBunkerToken;
        $this->xBunkerTenant = $xBunkerTenant;
    }

    private function makeRequest($endpoint, $method = 'POST', $data = null, $requestMetadata = null) {
        $headers = [
            'Content-Type: application/json'
        ];
        if ($this->xBunkerToken) {
            $headers[] = 'X-Bunker-Token: ' . $this->xBunkerToken;
        }
        if ($this->xBunkerTenant) {
            $headers[] = 'X-Bunker-Tenant: ' . $this->xBunkerTenant;
        }
        $url = $this->baseURL . '/v2/' . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($data || $requestMetadata) {
            $bodyData = $data ? $data : [];
            if ($requestMetadata) {
                $bodyData['request_metadata'] = $requestMetadata;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyData));
        }
        try {
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($response === false) {
                throw new Exception('cURL error: ' . curl_error($ch));
            }
            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error decoding JSON response: ' . json_last_error_msg());
            }
            curl_close($ch);
            return $result;
        } catch (Exception $e) {
            if (isset($ch)) {
                curl_close($ch);
            }
            throw new Exception('Error making request: ' . $e->getMessage());
        }
    }

    private function rawRequest($endpoint, $method = 'POST', $data = null, $requestMetadata = null) {
        $headers = [
            'Content-Type: application/json'
        ];

        if ($this->xBunkerToken) {
            $headers[] = 'X-Bunker-Token: ' . $this->xBunkerToken;
        }

        $options = [
            'http' => [
                'method' => $method,
                'header' => $headers
            ]
        ];

        if ($data || $requestMetadata) {
            $bodyData = $data ? $data : [];
            if ($requestMetadata) {
                $bodyData['request_metadata'] = $requestMetadata;
            }
            $options['http']['content'] = json_encode($bodyData);
        }

        $context = stream_context_create($options);
        return file_get_contents($this->baseURL . '/v2/' . $endpoint, false, $context);
    }

    // User Management
    public function createUser($profile, $options = [], $requestMetadata = null) {
        $data = ['profile' => $profile];
        
        if (isset($options['groupname'])) {
            if (is_numeric($options['groupname']) && intval($options['groupname']) == $options['groupname']) {
                $data['groupid'] = $options['groupname'];
            } else {
                $data['groupname'] = $options['groupname'];
            }
        } elseif (isset($options['groupid'])) {
            $data['groupid'] = $options['groupid'];
        }

        if (isset($options['rolename'])) {
            if (is_numeric($options['rolename']) && intval($options['rolename']) == $options['rolename']) {
                $data['roleid'] = $options['rolename'];
            } else {
                $data['rolename'] = $options['rolename'];
            }
        } elseif (isset($options['roleid'])) {
            $data['roleid'] = $options['roleid'];
        }

        if (isset($options['slidingtime'])) {
            $data['slidingtime'] = $options['slidingtime'];
        }
        if (isset($options['finaltime'])) {
            $data['finaltime'] = $options['finaltime'];
        }

        return $this->makeRequest('UserCreate', 'POST', $data, $requestMetadata);
    }

    public function getUser($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('UserGet', 'POST', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    public function deleteUser($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('UserDelete', 'POST', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    public function requestUserDeletion($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('UserDeleteRequest', 'POST', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    public function updateUser($mode, $identity, $profile, $requestMetadata = null) {
        return $this->makeRequest('UserUpdate', 'POST', ['mode' => $mode, 'identity' => $identity, 'profile' => $profile], $requestMetadata);
    }

    public function requestUserUpdate($mode, $identity, $profile, $requestMetadata = null) {
        return $this->makeRequest('UserUpdateRequest', 'POST', ['mode' => $mode, 'identity' => $identity, 'profile' => $profile], $requestMetadata);
    }

    public function preloginUser($mode, $identity, $code, $captchacode, $requestMetadata = null) {
        return $this->makeRequest('UserPrelogin', 'POST', ['mode' => $mode, 'identity' => $identity, 'code' => $code, 'captchacode' => $captchacode], $requestMetadata);
    }

    public function loginUser($mode, $identity, $smscode, $requestMetadata = null) {
        return $this->makeRequest('UserLogin', 'POST', ['mode' => $mode, 'identity' => $identity, 'smscode' => $smscode], $requestMetadata);
    }

    // User Request Management
    public function getUserRequest($requestuuid, $requestMetadata = null) {
        return $this->makeRequest('UserRequestGet', 'POST', ['requestuuid' => $requestuuid], $requestMetadata);
    }

    public function listUserRequests($mode, $identity, $offset = 0, $limit = 10, $requestMetadata = null) {
        return $this->makeRequest('UserRequestListUserRequests', 'POST', ['mode' => $mode, 'identity' => $identity, 'offset' => $offset, 'limit' => $limit], $requestMetadata);
    }

    public function cancelUserRequest($requestuuid, $reason = null, $requestMetadata = null) {
        return $this->makeRequest('UserRequestCancel', 'POST', ['requestuuid' => $requestuuid, 'reason' => $reason], $requestMetadata);
    }

    public function approveUserRequest($requestuuid, $reason = null, $requestMetadata = null) {
        return $this->makeRequest('UserRequestApprove', 'POST', ['requestuuid' => $requestuuid, 'reason' => $reason], $requestMetadata);
    }

    // App Data Management
    public function createAppData($mode, $identity, $appname, $data, $requestMetadata = null) {
        return $this->makeRequest('AppdataCreate', 'POST', ['mode' => $mode, 'identity' => $identity, 'appname' => $appname, 'data' => $data], $requestMetadata);
    }

    public function getUserAppData($mode, $identity, $appname, $requestMetadata = null) {
        return $this->makeRequest('AppdataGet', 'POST', ['mode' => $mode, 'identity' => $identity, 'appname' => $appname], $requestMetadata);
    }

    public function updateAppData($mode, $identity, $appname, $data, $requestMetadata = null) {
        return $this->makeRequest('AppdataUpdate', 'POST', ['mode' => $mode, 'identity' => $identity, 'appname' => $appname, 'data' => $data], $requestMetadata);
    }

    public function requestAppDataUpdate($mode, $identity, $appname, $data, $requestMetadata = null) {
        return $this->makeRequest('AppdataUpdateRequest', 'POST', ['mode' => $mode, 'identity' => $identity, 'appname' => $appname, 'data' => $data], $requestMetadata);
    }

    public function listUserAppDataRecords($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('AppdataListUserAppNames', 'POST', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    public function listAppNames($requestMetadata = null) {
        return $this->makeRequest('AppdataListAppNames', 'POST', null, $requestMetadata);
    }

    // Legal Basis Management
    public function createLegalBasis($options, $requestMetadata = null) {
        return $this->makeRequest('LegalBasisCreate', 'POST', $options, $requestMetadata);
    }

    public function updateLegalBasis($options, $requestMetadata = null) {
        return $this->makeRequest('LegalBasisUpdate', 'POST', $options, $requestMetadata);
    }

    public function listAgreements($requestMetadata = null) {
        return $this->makeRequest('LegalBasisListAgreements', 'POST', null, $requestMetadata);
    }

    // Agreement Management
    public function acceptAgreement($mode, $identity, $options, $requestMetadata = null) {
        $data = array_merge(['mode' => $mode, 'identity' => $identity], $options);
        return $this->makeRequest('AgreementAccept', 'POST', $data, $requestMetadata);
    }

    public function cancelAgreement($mode, $identity, $brief, $requestMetadata = null) {
        return $this->makeRequest('AgreementCancel', 'POST', ['mode' => $mode, 'identity' => $identity, 'brief' => $brief], $requestMetadata);
    }

    public function requestAgreementCancellation($mode, $identity, $brief, $requestMetadata = null) {
        return $this->makeRequest('AgreementCancelRequest', 'POST', ['mode' => $mode, 'identity' => $identity, 'brief' => $brief], $requestMetadata);
    }

    public function getUserAgreement($mode, $identity, $brief, $requestMetadata = null) {
        return $this->makeRequest('AgreementGet', 'POST', ['mode' => $mode, 'identity' => $identity, 'brief' => $brief], $requestMetadata);
    }

    public function listUserAgreements($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('AgreementListUserAgreements', 'POST', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    // Processing Activity Management
    public function listProcessingActivities($requestMetadata = null) {
        return $this->makeRequest('ProcessingActivityListActivities', 'POST', null, $requestMetadata);
    }

    // Connector Management
    public function listSupportedConnectors($requestMetadata = null) {
        return $this->makeRequest('ConnectorListSupportedConnectors', 'POST', null, $requestMetadata);
    }

    public function listConnectors($offset = 0, $limit = 10, $requestMetadata = null) {
        return $this->makeRequest('ConnectorListConnectors', 'POST', ['offset' => $offset, 'limit' => $limit], $requestMetadata);
    }

    public function createConnector($options, $requestMetadata = null) {
        return $this->makeRequest('ConnectorCreate', 'POST', $options, $requestMetadata);
    }

    public function updateConnector($options, $requestMetadata = null) {
        return $this->makeRequest('ConnectorUpdate', 'POST', $options, $requestMetadata);
    }

    public function validateConnectorConnectivity($options, $requestMetadata = null) {
        return $this->makeRequest('ConnectorValidateConnectivity', 'POST', $options, $requestMetadata);
    }

    public function deleteConnector($connectorid, $requestMetadata = null) {
        return $this->makeRequest('ConnectorDelete', 'POST', ['connectorid' => $connectorid], $requestMetadata);
    }

    public function getTableMetadata($options, $requestMetadata = null) {
        return $this->makeRequest('ConnectorGetTableMetaData', 'POST', $options, $requestMetadata);
    }

    public function connectorGetUserData($mode, $identity, $connectorid, $requestMetadata = null) {
        return $this->makeRequest('ConnectorGetUserData', 'POST', ['mode' => $mode, 'identity' => $identity, 'connectorid' => $connectorid], $requestMetadata);
    }

    public function connectorGetUserExtraData($mode, $identity, $connectorid, $requestMetadata = null) {
        return $this->makeRequest('ConnectorGetUserExtraData', 'POST', ['mode' => $mode, 'identity' => $identity, 'connectorid' => $connectorid], $requestMetadata);
    }

    public function connectorDeleteUser($mode, $identity, $connectorid, $requestMetadata = null) {
        return $this->makeRequest('ConnectorDeleteUser', 'POST', ['mode' => $mode, 'identity' => $identity, 'connectorid' => $connectorid], $requestMetadata);
    }

    // Group Management
    public function createGroup($groupname, $groupdesc = '', $requestMetadata = null) {
        return $this->makeRequest('GroupCreate', 'POST', ['groupname' => $groupname, 'groupdesc' => $groupdesc], $requestMetadata);
    }

    public function getGroup($groupid, $requestMetadata = null) {
        return $this->makeRequest('GroupGet', 'POST', ['groupid' => $groupid], $requestMetadata);
    }

    public function listAllGroups($requestMetadata = null) {
        return $this->makeRequest('GroupListAllGroups', 'POST', null, $requestMetadata);
    }

    public function addUserToGroup($mode, $identity, $groupname, $rolename = null, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        
        if (is_numeric($groupname) && intval($groupname) == $groupname) {
            $data['groupid'] = $groupname;
        } else {
            $data['groupname'] = $groupname;
        }

        if ($rolename) {
            if (is_numeric($rolename) && intval($rolename) == $rolename) {
                $data['roleid'] = $rolename;
            } else {
                $data['rolename'] = $rolename;
            }
        }

        return $this->makeRequest('GroupAddUser', 'POST', $data, $requestMetadata);
    }

    // Access Token Management
    public function createXToken($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('XTokenCreate', 'POST', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    // Sensitive Records Tokenization API
    public function createToken($tokentype, $record, $requestMetadata = null) {
        $data = ['tokentype' => $tokentype, 'record' => $record];
        return $this->makeRequest('TokenCreate', 'POST', $data, $requestMetadata);
    }

    public function createTokensBulk($records, $requestMetadata = null) {
        return $this->makeRequest('TokenCreateBulk', 'POST', ['records' => $records], $requestMetadata);
    }

    public function getToken($token, $requestMetadata = null) {
        return $this->makeRequest('TokenGet', 'POST', ['token' => $token], $requestMetadata);
    }

    public function deleteToken($token, $requestMetadata = null) {
        return $this->makeRequest('TokenDelete', 'POST', ['token' => $token], $requestMetadata);
    }

    public function listTokensBulk($tokens, $requestMetadata = null) {
        return $this->makeRequest('TokenListBulk', 'POST', ['tokens' => $tokens], $requestMetadata);
    }

    public function deleteTokensBulk($tokens, $requestMetadata = null) {
        return $this->makeRequest('TokenDeleteBulk', 'POST', ['tokens' => $tokens], $requestMetadata);
    }

    // Audit Management
    public function listUserAuditEvents($mode, $identity, $offset = 0, $limit = 10, $requestMetadata = null) {
        return $this->makeRequest('AuditListUserEvents', 'POST', ['mode' => $mode, 'identity' => $identity, 'offset' => $offset, 'limit' => $limit], $requestMetadata);
    }

    public function getAuditEvent($auditeventuuid, $requestMetadata = null) {
        return $this->makeRequest('AuditGetEvent', 'POST', ['auditeventuuid' => $auditeventuuid], $requestMetadata);
    }

    // Tenant Management
    public function createTenant($data, $requestMetadata = null) {
        return $this->makeRequest('TenantCreate', 'POST', $data, $requestMetadata);
    }

    public function getTenant($tenantid, $requestMetadata = null) {
        return $this->makeRequest('TenantGet', 'POST', ['tenantid' => $tenantid], $requestMetadata);
    }

    public function updateTenant($tenantid, $tenantname, $tenantorg, $email, $requestMetadata = null) {
        return $this->makeRequest('TenantUpdate', 'POST', ['tenantid' => $tenantid, 'tenantname' => $tenantname, 'tenantorg' => $tenantorg, 'email' => $email], $requestMetadata);
    }

    public function deleteTenant($tenantid, $requestMetadata = null) {
        return $this->makeRequest('TenantDelete', 'POST', ['tenantid' => $tenantid], $requestMetadata);
    }

    public function listTenants($offset = 0, $limit = 10, $requestMetadata = null) {
        return $this->makeRequest('TenantListTenants', 'POST', ['offset' => $offset, 'limit' => $limit], $requestMetadata);
    }

    // Role Management
    public function createRole($rolename, $requestMetadata = null) {
        return $this->makeRequest('RoleCreate', 'POST', ['rolename' => $rolename], $requestMetadata);
    }

    public function linkPolicy($rolename, $policyname, $requestMetadata = null) {
        return $this->makeRequest('RoleLinkPolicy', 'POST', ['rolename' => $rolename, 'policyname' => $policyname], $requestMetadata);
    }

    // Policy Management
    public function createPolicy($data, $requestMetadata = null) {
        return $this->makeRequest('PolicyCreate', 'POST', $data, $requestMetadata);
    }

    public function updatePolicy($policyid, $data, $requestMetadata = null) {
        return $this->makeRequest('PolicyUpdate', 'POST', array_merge(['policyid' => $policyid], $data), $requestMetadata);
    }

    public function getPolicy($policyname, $requestMetadata = null) {
        $data = [];
        if ($policyname) {
            if (is_numeric($policyname) && intval($policyname) == $policyname) {
                $data['policyid'] = $policyname;
            } else {
                $data['policyname'] = $policyname;
            }
        }
        return $this->makeRequest('PolicyGet', 'POST', $data, $requestMetadata);
    }

    public function listPolicies($requestMetadata = null) {
        return $this->makeRequest('PolicyListAllPolicies', 'POST', null, $requestMetadata);
    }

    // Bulk Operations
    public function bulkListUnlock($requestMetadata = null) {
        return $this->makeRequest('BulkListUnlock', 'POST', null, $requestMetadata);
    }

    public function bulkListUsers($unlockuuid, $offset = 0, $limit = 10, $requestMetadata = null) {
        return $this->makeRequest('BulkListUsers', 'POST', ['unlockuuid' => $unlockuuid, 'offset' => $offset, 'limit' => $limit], $requestMetadata);
    }

    public function bulkListGroupUsers($unlockuuid, $groupname, $offset = 0, $limit = 10, $requestMetadata = null) {
        $data = ['unlockuuid' => $unlockuuid, 'offset' => $offset, 'limit' => $limit];
        if (is_numeric($groupname) && intval($groupname) == $groupname) {
            $data['groupid'] = $groupname;
        } else {
            $data['groupname'] = $groupname;
        }
        return $this->makeRequest('BulkListGroupUsers', 'POST', $data, $requestMetadata);
    }

    public function bulkListUserRequests($unlockuuid, $offset = 0, $limit = 10, $requestMetadata = null) {
        return $this->makeRequest('BulkListUserRequests', 'POST', ['unlockuuid' => $unlockuuid, 'offset' => $offset, 'limit' => $limit], $requestMetadata);
    }

    public function bulkListAuditEvents($unlockuuid, $offset = 0, $limit = 10, $requestMetadata = null) {
        return $this->makeRequest('BulkListAuditEvents', 'POST', ['unlockuuid' => $unlockuuid, 'offset' => $offset, 'limit' => $limit], $requestMetadata);
    }

    // System Configuration
    public function getUIConf() {
        return $this->makeRequest('TenantGetUIConf', 'POST');
    }

    public function getTenantConf() {
        return $this->makeRequest('TenantGetConf', 'POST');
    }

    public function getUserHTMLReport($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('SystemGetUserHTMLReport', 'POST', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    public function getUserReport($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('SystemGetUserReport', 'POST', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    // Session Management
    public function upsertSession($sessionuuid, $data, $requestMetadata = null) {
        return $this->makeRequest('SessionUpsert', 'POST', array_merge(['sessionuuid' => $sessionuuid], $data), $requestMetadata);
    }

    public function getSession($sessionuuid, $requestMetadata = null) {
        return $this->makeRequest('SessionGet', 'POST', ['sessionuuid' => $sessionuuid], $requestMetadata);
    }

    public function deleteSession($sessionuuid, $requestMetadata = null) {
        return $this->makeRequest('SessionDelete', 'POST', ['sessionuuid' => $sessionuuid], $requestMetadata);
    }
    
    /**
     * Gets system statistics
     * @param array|null $requestMetadata Additional metadata to include with the request
     * @return array System statistics
     * 
     * Response format:
     * {
     *   "status": "ok",
     *   "stats": {
     *     "numusers": 123, // Total number of users in the system
     *     "numtenants": 123, // Total number of tenants
     *     "numtokens": 123, // Total number of tokens
     *     "numsessions": 123 // Total number of active sessions
     *   }
     * }
     */
    public function getSystemStats($requestMetadata = null) {
        return $this->makeRequest('SystemGetSystemStats', 'POST', null, $requestMetadata);
    }

    /**
     * Parses Prometheus metrics text into a structured array
     * @param string $metricsText The raw metrics text from Prometheus
     * @return array Parsed metrics
     */
    public function parsePrometheusMetrics($metricsText) {
        $lines = explode("\n", $metricsText);
        $metrics = [];
        
        foreach ($lines as $line) {
            // Skip comments and empty lines
            if (strpos(trim($line), '#') === 0 || empty(trim($line))) continue;
            
            // Parse metric line
            if (preg_match('/^([a-zA-Z0-9_]+)(?:{([^}]+)})?\s+([0-9.]+)$/', $line, $matches)) {
                $name = $matches[1];
                $labels = isset($matches[2]) ? $matches[2] : '';
                $value = floatval($matches[3]);
                
                $metricKey = $labels ? "{$name}{{$labels}}" : $name;
                $metrics[$metricKey] = $value;
            }
        }
        
        return $metrics;
    }

    /**
     * Gets system metrics from the Prometheus endpoint
     * @param array|null $requestMetadata Additional metadata to include with the request
     * @return array Parsed system metrics
     */
    public function getSystemMetrics($requestMetadata = null) {
        // Call /metrics endpoint
        $url = $this->baseURL . '/metrics';
        
        $headers = [];
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => $headers
            ]
        ];
        try {
            $context = stream_context_create($options);
            $metricsText = file_get_contents($url, false, $context);
            if ($metricsText === false) {
                throw new Exception('Failed to fetch metrics');
            }
            return $this->parsePrometheusMetrics($metricsText);
        } catch (Exception $error) {
            error_log('Error fetching metrics: ' . $error->getMessage());
            return [];
        }
    }
} 