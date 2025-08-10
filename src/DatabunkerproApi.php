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

    private function makeRequest($endpoint, $data = null, $requestMetadata = null) {
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
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
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

    public function rawRequest($endpoint, $data = null, $requestMetadata = null) {
        $headers = [
            'Content-Type: application/json'
        ];
        if ($this->xBunkerToken) {
            $headers[] = 'X-Bunker-Token: ' . $this->xBunkerToken;
        }
        $url = $this->baseURL . '/v2/' . $endpoint;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($data || $requestMetadata) {
            $bodyData = $data ? $data : [];
            if ($requestMetadata) {
                $bodyData['request_metadata'] = $requestMetadata;
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyData));
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
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
        return $this->makeRequest('UserCreate', $data, $requestMetadata);
    }

    public function createUsersBulk($records, $options = [], $requestMetadata = null) {
        $data = [
            'records' => array_map(function($record) {
                $userData = ['profile' => $record['profile']];
                
                if (isset($record['groupname'])) {
                    if (is_numeric($record['groupname']) && intval($record['groupname']) == $record['groupname']) {
                        $userData['groupid'] = $record['groupname'];
                    } else {
                        $userData['groupname'] = $record['groupname'];
                    }
                } elseif (isset($record['groupid'])) {
                    $userData['groupid'] = $record['groupid'];
                }

                if (isset($record['rolename'])) {
                    if (is_numeric($record['rolename']) && intval($record['rolename']) == $record['rolename']) {
                        $userData['roleid'] = $record['rolename'];
                    } else {
                        $userData['rolename'] = $record['rolename'];
                    }
                } elseif (isset($record['roleid'])) {
                    $userData['roleid'] = $record['roleid'];
                }
                
                return $userData;
            }, $records)
        ];

        if (isset($options['finaltime'])) {
            $data['finaltime'] = $options['finaltime'];
        }
        if (isset($options['slidingtime'])) {
            $data['slidingtime'] = $options['slidingtime'];
        }

        return $this->makeRequest('UserCreateBulk', $data, $requestMetadata);
    }

    public function getUser($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('UserGet', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    public function updateUser($mode, $identity, $profile, $requestMetadata = null) {
        return $this->makeRequest('UserUpdate', ['mode' => $mode, 'identity' => $identity, 'profile' => $profile], $requestMetadata);
    }

    public function requestUserUpdate($mode, $identity, $profile, $requestMetadata = null) {
        return $this->makeRequest('UserUpdateRequest', ['mode' => $mode, 'identity' => $identity, 'profile' => $profile], $requestMetadata);
    }

    public function patchUser($mode, $identity, $patch, $requestMetadata = null) {
        return $this->makeRequest('UserPatch', ['mode' => $mode, 'identity' => $identity, 'patch' => $patch], $requestMetadata);
    }

    public function requestUserPatch($mode, $identity, $patch, $requestMetadata = null) {
        return $this->makeRequest('UserPatchRequest', ['mode' => $mode, 'identity' => $identity, 'patch' => $patch], $requestMetadata);
    }

    public function deleteUser($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('UserDelete', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    public function requestUserDeletion($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('UserDeleteRequest', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    // User Authentication
    public function preloginUser($mode, $identity, $code, $captchacode, $requestMetadata = null) {
        return $this->makeRequest('UserPrelogin', ['mode' => $mode, 'identity' => $identity, 'code' => $code, 'captchacode' => $captchacode], $requestMetadata);
    }

    public function loginUser($mode, $identity, $smscode, $requestMetadata = null) {
        return $this->makeRequest('UserLogin', ['mode' => $mode, 'identity' => $identity, 'smscode' => $smscode], $requestMetadata);
    }

    public function createCaptcha($requestMetadata = null) {
        return $this->makeRequest('CaptchaCreate', null, $requestMetadata);
    }

    // Create user API Access Token
    public function createUserXToken($mode, $identity, $options = [], $requestMetadata = null) {
        $data = array_merge(['mode' => $mode, 'identity' => $identity], $options);
        return $this->makeRequest('XTokenCreateForUser', $data, $requestMetadata);
    }

    public function createRoleXToken($roleref, $options = [], $requestMetadata = null) {
        $data = $options;
        if (is_numeric($roleref) && intval($roleref) == $roleref) {
            $data['roleid'] = $roleref;
        } else {
            $data['rolename'] = $roleref;
        }
        return $this->makeRequest('XTokenCreateForRole', $data, $requestMetadata);
    }

    // User Request Management
    public function getUserRequest($requestuuid, $requestMetadata = null) {
        return $this->makeRequest('UserRequestGet', ['requestuuid' => $requestuuid], $requestMetadata);
    }

    public function listUserRequests($mode, $identity, $offset = 0, $limit = 10, $requestMetadata = null) {
        return $this->makeRequest('UserRequestListUserRequests', ['mode' => $mode, 'identity' => $identity, 'offset' => $offset, 'limit' => $limit], $requestMetadata);
    }

    public function cancelUserRequest($requestuuid, $options = [], $requestMetadata = null) {
        $data = ['requestuuid' => $requestuuid];
        if (isset($options['reason'])) {
            $data['reason'] = $options['reason'];
        }
        return $this->makeRequest('UserRequestCancel', $data, $requestMetadata);
    }

    public function approveUserRequest($requestuuid, $options = [], $requestMetadata = null) {
        $data = ['requestuuid' => $requestuuid];
        if (isset($options['reason'])) {
            $data['reason'] = $options['reason'];
        }
        return $this->makeRequest('UserRequestApprove', $data, $requestMetadata);
    }

    // App Data Management
    public function createAppData($mode, $identity, $appname, $appdata, $requestMetadata = null) {
        return $this->makeRequest('AppdataCreate', ['mode' => $mode, 'identity' => $identity, 'appname' => $appname, 'appdata' => $appdata], $requestMetadata);
    }

    public function getAppData($mode, $identity, $appname, $requestMetadata = null) {
        return $this->makeRequest('AppdataGet', ['mode' => $mode, 'identity' => $identity, 'appname' => $appname], $requestMetadata);
    }

    public function updateAppData($mode, $identity, $appname, $appdata, $requestMetadata = null) {
        return $this->makeRequest('AppdataUpdate', ['mode' => $mode, 'identity' => $identity, 'appname' => $appname, 'appdata' => $appdata], $requestMetadata);
    }

    public function requestAppDataUpdate($mode, $identity, $appname, $appdata, $requestMetadata = null) {
        return $this->makeRequest('AppdataUpdateRequest', ['mode' => $mode, 'identity' => $identity, 'appname' => $appname, 'appdata' => $appdata], $requestMetadata);
    }

    public function listAppDataNames($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('AppdataListUserAppNames', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    public function listAppNames($requestMetadata = null) {
        return $this->makeRequest('AppdataListAppNames', null, $requestMetadata);
    }

    // Legal Basis Management
    public function createLegalBasis($options, $requestMetadata = null) {
        return $this->makeRequest('LegalBasisCreate', $options, $requestMetadata);
    }

    public function updateLegalBasis($brief, $options, $requestMetadata = null) {
        $data = array_merge(['brief' => $brief], $options);
        return $this->makeRequest('LegalBasisUpdate', $data, $requestMetadata);
    }

    public function deleteLegalBasis($brief, $requestMetadata = null) {
        return $this->makeRequest('LegalBasisDelete', ['brief' => $brief], $requestMetadata);
    }

    public function listAgreements($requestMetadata = null) {
        return $this->makeRequest('LegalBasisListAgreements', null, $requestMetadata);
    }

    // Agreement Management
    public function acceptAgreement($mode, $identity, $brief, $options = [], $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity, 'brief' => $brief];
        if (isset($options['agreementmethod'])) {
            $data['agreementmethod'] = $options['agreementmethod'];
        }
        if (isset($options['lastmodifiedby'])) {
            $data['lastmodifiedby'] = $options['lastmodifiedby'];
        }
        if (isset($options['referencecode'])) {
            $data['referencecode'] = $options['referencecode'];
        }
        if (isset($options['starttime'])) {
            $data['starttime'] = $options['starttime'];
        }
        if (isset($options['finaltime'])) {
            $data['finaltime'] = $options['finaltime'];
        }
        if (isset($options['status'])) {
            $data['status'] = $options['status'];
        }
        return $this->makeRequest('AgreementAccept', $data, $requestMetadata);
    }

    public function getUserAgreement($mode, $identity, $brief, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity, 'brief' => $brief];
        return $this->makeRequest('AgreementGet', $data, $requestMetadata);
    }

    public function listUserAgreements($mode, $identity, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        return $this->makeRequest('AgreementListUserAgreements', $data, $requestMetadata);
    }

    public function cancelAgreement($mode, $identity, $brief, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity, 'brief' => $brief];
        return $this->makeRequest('AgreementCancel', $data, $requestMetadata);
    }

    public function requestAgreementCancellation($mode, $identity, $brief, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity, 'brief' => $brief];
        return $this->makeRequest('AgreementCancelRequest', $data, $requestMetadata);
    }

    public function revokeAllAgreements($brief, $requestMetadata = null) {
        $data = ['brief' => $brief];
        return $this->makeRequest('AgreementRevokeAll', $data, $requestMetadata);
    }

    // Processing Activity Management
    public function listProcessingActivities($requestMetadata = null) {
        return $this->makeRequest('ProcessingActivityListActivities', null, $requestMetadata);
    }

    public function createProcessingActivity($options, $requestMetadata = null) {
        return $this->makeRequest('ProcessingActivityCreate', $options, $requestMetadata);
    }

    public function updateProcessingActivity($activity, $options, $requestMetadata = null) {
        $data = array_merge(['activity' => $activity], $options);
        return $this->makeRequest('ProcessingActivityUpdate', $data, $requestMetadata);
    }

    public function deleteProcessingActivity($activity, $requestMetadata = null) {
        $data = ['activity' => $activity];
        return $this->makeRequest('ProcessingActivityDelete', $data, $requestMetadata);
    }

    public function linkProcessingActivityToLegalBasis($activity, $brief, $requestMetadata = null) {
        $data = ['activity' => $activity, 'brief' => $brief];
        return $this->makeRequest('ProcessingActivityLinkLegalBasis', $data, $requestMetadata);
    }

    public function unlinkProcessingActivityFromLegalBasis($activity, $brief, $requestMetadata = null) {
        $data = ['activity' => $activity, 'brief' => $brief];
        return $this->makeRequest('ProcessingActivityUnlinkLegalBasis', $data, $requestMetadata);
    }

    // Connector Management
    public function listSupportedConnectors($requestMetadata = null) {
        return $this->makeRequest('ConnectorListSupportedConnectors', null, $requestMetadata);
    }

    public function listConnectors($offset = 0, $limit = 10, $requestMetadata = null) {
        $data = ['offset' => $offset, 'limit' => $limit];
        return $this->makeRequest('ConnectorListConnectors', $data, $requestMetadata);
    }

    public function createConnector($options, $requestMetadata = null) {
        return $this->makeRequest('ConnectorCreate', $options, $requestMetadata);
    }

    public function updateConnector($connectorid, $options, $requestMetadata = null) {
        $data = array_merge(['connectorid' => $connectorid], $options);
        return $this->makeRequest('ConnectorUpdate', $data, $requestMetadata);
    }

    public function validateConnectorConnectivity($connectorref, $options = [], $requestMetadata = null) {
        $data = $options;
        if (is_numeric($connectorref) && intval($connectorref) == $connectorref) {
            $data['connectorid'] = $connectorref;
        } else {
            $data['connectorname'] = $connectorref;
        }
        return $this->makeRequest('ConnectorValidateConnectivity', $data, $requestMetadata);
    }

    public function deleteConnector($connectorref, $requestMetadata = null) {
        $data = [];
        if (is_numeric($connectorref) && intval($connectorref) == $connectorref) {
            $data['connectorid'] = $connectorref;
        } else {
            $data['connectorname'] = $connectorref;
        }
        return $this->makeRequest('ConnectorDelete', $data, $requestMetadata);
    }

    public function getTableMetadata($connectorref, $options = [], $requestMetadata = null) {
        $data = $options;
        if (is_numeric($connectorref) && intval($connectorref) == $connectorref) {
            $data['connectorid'] = $connectorref;
        } else {
            $data['connectorname'] = $connectorref;
        }
        return $this->makeRequest('ConnectorGetTableMetaData', $data, $requestMetadata);
    }

    public function connectorGetUserData($mode, $identity, $connectorref, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        if (is_numeric($connectorref) && intval($connectorref) == $connectorref) {
            $data['connectorid'] = $connectorref;
        } else {
            $data['connectorname'] = $connectorref;
        }
        return $this->makeRequest('ConnectorGetUserData', $data, $requestMetadata);
    }

    public function connectorGetUserExtraData($mode, $identity, $connectorref, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        if (is_numeric($connectorref) && intval($connectorref) == $connectorref) {
            $data['connectorid'] = $connectorref;
        } else {
            $data['connectorname'] = $connectorref;
        }
        return $this->makeRequest('ConnectorGetUserExtraData', $data, $requestMetadata);
    }

    public function connectorDeleteUser($mode, $identity, $connectorref, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        if (is_numeric($connectorref) && intval($connectorref) == $connectorref) {
            $data['connectorid'] = $connectorref;
        } else {
            $data['connectorname'] = $connectorref;
        }
        return $this->makeRequest('ConnectorDeleteUser', $data, $requestMetadata);
    }

    // Group Management
    public function createGroup($options, $requestMetadata = null) {
        return $this->makeRequest('GroupCreate', $options, $requestMetadata);
    }

    public function getGroup($groupref, $requestMetadata = null) {
        $data = [];
        if (is_numeric($groupref) && intval($groupref) == $groupref) {
            $data['groupid'] = $groupref;
        } else {
            $data['groupname'] = $groupref;
        }
        return $this->makeRequest('GroupGet', $data, $requestMetadata);
    }

    public function listAllGroups($requestMetadata = null) {
        return $this->makeRequest('GroupListAllGroups', null, $requestMetadata);
    }

    public function listUserGroups($mode, $identity, $requestMetadata = null) {
        return $this->makeRequest('GroupListUserGroups', ['mode' => $mode, 'identity' => $identity], $requestMetadata);
    }

    public function updateGroup($groupid, $options, $requestMetadata = null) {
        $data = array_merge(['groupid' => $groupid], $options);
        return $this->makeRequest('GroupUpdate', $data, $requestMetadata);
    }

    public function deleteGroup($groupref, $requestMetadata = null) {
        $data = [];
        if (is_numeric($groupref) && intval($groupref) == $groupref) {
            $data['groupid'] = $groupref;
        } else {
            $data['groupname'] = $groupref;
        }
        return $this->makeRequest('GroupDelete', $data, $requestMetadata);
    }

    public function removeUserFromGroup($mode, $identity, $groupref, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        if (is_numeric($groupref) && intval($groupref) == $groupref) {
            $data['groupid'] = $groupref;
        } else {
            $data['groupname'] = $groupref;
        }
        return $this->makeRequest('GroupDeleteUser', $data, $requestMetadata);
    }

    public function addUserToGroup($mode, $identity, $groupref, $roleref = null, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        if (is_numeric($groupref) && intval($groupref) == $groupref) {
            $data['groupid'] = $groupref;
        } else {
            $data['groupname'] = $groupref;
        }
        if ($roleref) {
            if (is_numeric($roleref) && intval($roleref) == $roleref) {
                $data['roleid'] = $roleref;
            } else {
                $data['rolename'] = $roleref;
            }
        }
        return $this->makeRequest('GroupAddUser', $data, $requestMetadata);
    }

    // Token Management (for example for credit cards)
    public function createToken($tokentype, $record, $options = [], $requestMetadata = null) {
        $data = array_merge(['tokentype' => $tokentype, 'record' => $record], $options);
        return $this->makeRequest('TokenCreate', $data, $requestMetadata);
    }

    public function createTokensBulk($records, $options = [], $requestMetadata = null) {
        $data = array_merge(['records' => $records], $options);
        return $this->makeRequest('TokenCreateBulk', $data, $requestMetadata);
    }

    public function getToken($token, $requestMetadata = null) {
        $data = ['token' => $token];
        return $this->makeRequest('TokenGet', $data, $requestMetadata);
    }

    public function deleteToken($token, $requestMetadata = null) {
        $data = ['token' => $token];
        return $this->makeRequest('TokenDelete', $data, $requestMetadata);
    }

    // Audit Management
    public function listUserAuditEvents($mode, $identity, $offset = 0, $limit = 10, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity, 'offset' => $offset, 'limit' => $limit];
        return $this->makeRequest('AuditListUserEvents', $data, $requestMetadata);
    }

    public function getAuditEvent($auditeventuuid, $requestMetadata = null) {
        $data = ['auditeventuuid' => $auditeventuuid];
        return $this->makeRequest('AuditGetEvent', $data, $requestMetadata);
    }

    // Tenant Management
    public function createTenant($options, $requestMetadata = null) {
        return $this->makeRequest('TenantCreate', $options, $requestMetadata);
    }

    public function getTenant($tenantid, $requestMetadata = null) {
        $data = ['tenantid' => $tenantid];
        return $this->makeRequest('TenantGet', $data, $requestMetadata);
    }

    public function updateTenant($tenantid, $options, $requestMetadata = null) {
        $data = array_merge(['tenantid' => $tenantid], $options);
        return $this->makeRequest('TenantUpdate', $data, $requestMetadata);
    }

    public function deleteTenant($tenantid, $requestMetadata = null) {
        $data = ['tenantid' => $tenantid];
        return $this->makeRequest('TenantDelete', $data, $requestMetadata);
    }

    public function listTenants($offset = 0, $limit = 10, $requestMetadata = null) {
        $data = ['offset' => $offset, 'limit' => $limit];
        return $this->makeRequest('TenantListTenants', $data, $requestMetadata);
    }

    // Role Management
    public function createRole($options, $requestMetadata = null) {
        return $this->makeRequest('RoleCreate', $options, $requestMetadata);
    }

    public function updateRole($roleid, $options, $requestMetadata = null) {
        $data = array_merge(['roleid' => $roleid], $options);
        return $this->makeRequest('RoleUpdate', $data, $requestMetadata);
    }

    public function linkPolicy($roleref, $policyref, $requestMetadata = null) {
        $data = [];
        if (is_numeric($roleref) && intval($roleref) == $roleref) {
            $data['roleid'] = $roleref;
        } else {
            $data['rolename'] = $roleref;
        }
        if (is_numeric($policyref) && intval($policyref) == $policyref) {
            $data['policyid'] = $policyref;
        } else {
            $data['policyname'] = $policyref;
        }
        return $this->makeRequest('RoleLinkPolicy', $data, $requestMetadata);
    }

    // Policy Management
    public function createPolicy($options, $requestMetadata = null) {
        return $this->makeRequest('PolicyCreate', $options, $requestMetadata);
    }

    public function updatePolicy($policyid, $options, $requestMetadata = null) {
        $data = array_merge(['policyid' => $policyid], $options);
        return $this->makeRequest('PolicyUpdate', $data, $requestMetadata);
    }

    public function getPolicy($policyref, $requestMetadata = null) {
        $data = [];
        if (is_numeric($policyref) && intval($policyref) == $policyref) {
            $data['policyid'] = $policyref;
        } else {
            $data['policyname'] = $policyref;
        }
        return $this->makeRequest('PolicyGet', $data, $requestMetadata);
    }

    public function listPolicies($requestMetadata = null) {
        return $this->makeRequest('PolicyListAllPolicies', null, $requestMetadata);
    }

    // Bulk Operations
    public function bulkListUnlock($requestMetadata = null) {
        return $this->makeRequest('BulkListUnlock', null, $requestMetadata);
    }

    public function bulkListUsers($unlockuuid, $offset = 0, $limit = 10, $requestMetadata = null) {
        $data = ['unlockuuid' => $unlockuuid, 'offset' => $offset, 'limit' => $limit];
        return $this->makeRequest('BulkListUsers', $data, $requestMetadata);
    }

    public function bulkListGroupUsers($unlockuuid, $groupref, $offset = 0, $limit = 10, $requestMetadata = null) {
        $data = ['unlockuuid' => $unlockuuid, 'offset' => $offset, 'limit' => $limit];
        if (is_numeric($groupref) && intval($groupref) == $groupref) {
            $data['groupid'] = $groupref;
        } else {
            $data['groupname'] = $groupref;
        }
        return $this->makeRequest('BulkListGroupUsers', $data, $requestMetadata);
    }

    public function bulkListUserRequests($unlockuuid, $offset = 0, $limit = 10, $requestMetadata = null) {
        $data = ['unlockuuid' => $unlockuuid, 'offset' => $offset, 'limit' => $limit];
        return $this->makeRequest('BulkListUserRequests', $data, $requestMetadata);
    }

    public function bulkListAuditEvents($unlockuuid, $offset = 0, $limit = 10, $requestMetadata = null) {
        $data = ['unlockuuid' => $unlockuuid, 'offset' => $offset, 'limit' => $limit];
        return $this->makeRequest('BulkListAuditEvents', $data, $requestMetadata);
    }

    public function bulkListTokens($unlockuuid, $tokens, $requestMetadata = null) {
        $data = ['unlockuuid' => $unlockuuid, 'tokens' => $tokens];
        return $this->makeRequest('BulkListTokens', $data, $requestMetadata);
    }

    public function bulkDeleteTokens($unlockuuid, $tokens, $requestMetadata = null) {
        $data = ['unlockuuid' => $unlockuuid, 'tokens' => $tokens];
        return $this->makeRequest('BulkDeleteTokens', $data, $requestMetadata);
    }

    // Session Management
    public function upsertSession($sessionuuid, $sessiondata, $options = [], $requestMetadata = null) {
        $data = array_merge(['sessionuuid' => $sessionuuid, 'sessiondata' => $sessiondata], $options);
        return $this->makeRequest('SessionUpsert', $data, $requestMetadata);
    }

    public function deleteSession($sessionuuid, $requestMetadata = null) {
        $data = ['sessionuuid' => $sessionuuid];
        return $this->makeRequest('SessionDelete', $data, $requestMetadata);
    }

    public function listUserSessions($mode, $identity, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        return $this->makeRequest('SessionListUserSessions', $data, $requestMetadata);
    }

    public function getSession($sessionuuid, $requestMetadata = null) {
        $data = ['sessionuuid' => $sessionuuid];
        return $this->makeRequest('SessionGet', $data, $requestMetadata);
    }

    // System Configuration
    public function getUIConf() {
        return $this->makeRequest('TenantGetUIConf');
    }

    public function getTenantConf() {
        return $this->makeRequest('TenantGetUIConf');
    }

    public function getUserHTMLReport($mode, $identity, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        return $this->makeRequest('SystemGetUserHTMLReport', $data, $requestMetadata);
    }

    public function getUserReport($mode, $identity, $requestMetadata = null) {
        $data = ['mode' => $mode, 'identity' => $identity];
        return $this->makeRequest('SystemGetUserReport', $data, $requestMetadata);
    }

    public function getSystemStats($requestMetadata = null) {
        return $this->makeRequest('SystemGetSystemStats', null, $requestMetadata);
    }

    /**
     * Generates a new wrapping key from three Shamir's Secret Sharing keys
     * @param string $key1 - First Shamir secret sharing key
     * @param string $key2 - Second Shamir secret sharing key
     * @param string $key3 - Third Shamir secret sharing key
     * @param array|null $requestMetadata - Additional metadata to include with the request
     * @return array The generated wrapping key
     *
     * Response format:
     * {
     *   "status": "ok",
     *   "wrappingkey": "generated-wrapping-key-value"
     * }
     */
    public function generateWrappingKey($key1, $key2, $key3, $requestMetadata = null) {
        $data = ['key1' => $key1, 'key2' => $key2, 'key3' => $key3];
        return $this->makeRequest('SystemGenerateWrappingKey', $data, $requestMetadata);
    }

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

    public function getSystemMetrics($requestMetadata = null) {
        $url = $this->baseURL . '/metrics';
        $headers = [];
        if ($this->xBunkerToken) {
            $headers[] = 'X-Bunker-Token: ' . $this->xBunkerToken;
        }
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

    public function createSharedRecord($mode, $identity, $options = [], $requestMetadata = null) {
        $data = [
            'mode' => $mode,
            'identity' => $identity
        ];
        if (isset($options['fields'])) {
            $data['fields'] = $options['fields'];
        }
        if (isset($options['partner'])) {
            $data['partner'] = $options['partner'];
        }
        if (isset($options['appname'])) {
            $data['appname'] = $options['appname'];
        }
        if (isset($options['finaltime'])) {
            $data['finaltime'] = $options['finaltime'];
        }
        return $this->makeRequest('SharedRecordCreate', $data, $requestMetadata);
    }

    public function getSharedRecord($recorduuid, $requestMetadata = null) {
        $data = ['recorduuid' => $recorduuid];
        return $this->makeRequest('SharedRecordGet', $data, $requestMetadata);
    }
}