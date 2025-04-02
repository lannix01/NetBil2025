<?php
require 'routeros_api.class.php';

class UserOperations {
    private $API;
    private $router_ip = '192.168.1.101';
    private $router_user = 'lannix';
    private $router_pass = 'lannix123NIC';

    public function __construct() {
        $this->API = new RouterosAPI();
        $this->connect();
    }

    private function connect() {
        if (!$this->API->connect($this->router_ip, $this->router_user, $this->router_pass)) {
            throw new Exception("Failed to connect to MikroTik router");
        }
    }

    // Data fetching methods
    public function getHotspotUsers() {
        return $this->API->comm('/ip/hotspot/user/print');
    }

    public function getActiveUsers() {
        return $this->API->comm('/ip/hotspot/active/print');
    }

    public function getProfiles() {
        return $this->API->comm('/ip/hotspot/user/profile/print');
    }

    // User actions
    public function addUser($data) {
        $params = [
            'name' => $data['username'],
            'password' => $data['password'],
            'profile' => $data['profile']
        ];

        if (!empty($data['limit'])) {
            $params['limit-bytes-total'] = $data['limit'] * 1024 * 1024;
        }

        $this->API->comm('/ip/hotspot/user/add', $params);
        return true;
    }

    public function updateUser($userId, $data) {
        $params = ['.id' => $userId];
        
        if (!empty($data['password'])) {
            $params['password'] = $data['password'];
        }
        
        if (!empty($data['profile'])) {
            $params['profile'] = $data['profile'];
        }
        
        if (isset($data['limit'])) {
            $params['limit-bytes-total'] = $data['limit'] * 1024 * 1024;
        }

        $this->API->comm('/ip/hotspot/user/set', $params);
        return true;
    }

    public function deleteUser($userId) {
        $this->API->comm('/ip/hotspot/user/remove', ['.id' => $userId]);
        return true;
    }

    public function __destruct() {
        $this->API->disconnect();
    }
}
?>