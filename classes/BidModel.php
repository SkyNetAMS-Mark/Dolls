<?php
/**
 * Bid Model
 */

class Bid {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $data['confirmation_token'] = generateToken(32);
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        return $this->db->insert('bids', $data);
    }
    
    public function getById($id) {
        $sql = "SELECT b.*, p.name as product_name, p.slug as product_slug
                FROM bids b
                LEFT JOIN products p ON b.product_id = p.id
                WHERE b.id = :id";
        
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    public function getByToken($token) {
        $sql = "SELECT b.*, p.name as product_name, p.slug as product_slug
                FROM bids b
                LEFT JOIN products p ON b.product_id = p.id
                WHERE b.confirmation_token = :token";
        
        return $this->db->fetchOne($sql, ['token' => $token]);
    }
    
    public function getByProduct($productId, $status = null) {
        $sql = "SELECT b.*, u.first_name, u.last_name
                FROM bids b
                LEFT JOIN users u ON b.user_id = u.id
                WHERE b.product_id = :product_id";
        
        $params = ['product_id' => $productId];
        
        if ($status) {
            $sql .= " AND b.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY b.bid_amount DESC, b.created_at ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getHighestBid($productId) {
        $sql = "SELECT b.*, u.first_name, u.last_name
                FROM bids b
                LEFT JOIN users u ON b.user_id = u.id
                WHERE b.product_id = :product_id 
                AND b.status = 'confirmed'
                ORDER BY b.bid_amount DESC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, ['product_id' => $productId]);
    }
    
    public function getByUser($userId) {
        $sql = "SELECT b.*, p.name as product_name, p.slug as product_slug, p.status as product_status,
                       (SELECT image_path FROM product_images 
                        WHERE product_id = p.id AND is_primary = 1 
                        LIMIT 1) as product_image
                FROM bids b
                LEFT JOIN products p ON b.product_id = p.id
                WHERE b.user_id = :user_id
                ORDER BY b.created_at DESC";
        
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    public function getByEmail($email) {
        $sql = "SELECT b.*, p.name as product_name, p.slug as product_slug, p.status as product_status
                FROM bids b
                LEFT JOIN products p ON b.product_id = p.id
                WHERE b.email = :email
                ORDER BY b.created_at DESC";
        
        return $this->db->fetchAll($sql, ['email' => $email]);
    }
    
    public function confirm($token) {
        $bid = $this->getByToken($token);
        
        if (!$bid) {
            return false;
        }
        
        if ($bid['status'] === 'confirmed') {
            return true;
        }
        
        $result = $this->db->update(
            'bids',
            [
                'status' => 'confirmed',
                'confirmed_at' => date('Y-m-d H:i:s')
            ],
            'confirmation_token = :token',
            ['token' => $token]
        );
        
        if ($result) {
            $highestBid = $this->getHighestBid($bid['product_id']);
            if ($highestBid && $highestBid['id'] == $bid['id']) {
                $productModel = new Product();
                $productModel->updateCurrentBid($bid['product_id'], $bid['bid_amount']);
            }
        }
        
        return $result;
    }
    
    public function updateStatus($id, $status) {
        return $this->db->update('bids', ['status' => $status], 'id = :id', ['id' => $id]);
    }
    
    public function acceptBid($id) {
        $this->db->beginTransaction();
        
        try {
            $bid = $this->getById($id);
            
            if (!$bid) {
                throw new Exception("Bid not found");
            }
            
            $this->updateStatus($id, 'accepted');
            
            $productModel = new Product();
            $productModel->update($bid['product_id'], ['status' => 'pending']);
            
            $this->db->query(
                "UPDATE bids SET status = 'rejected' 
                 WHERE product_id = :product_id AND id != :bid_id AND status IN ('pending', 'confirmed')",
                ['product_id' => $bid['product_id'], 'bid_id' => $id]
            );
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error accepting bid: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll($filters = [], $limit = BIDS_PER_PAGE, $offset = 0) {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['product_id'])) {
            $where[] = "b.product_id = :product_id";
            $params['product_id'] = $filters['product_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "b.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['email'])) {
            $where[] = "b.email LIKE :email";
            $params['email'] = '%' . $filters['email'] . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT b.*, p.name as product_name, p.slug as product_slug
                FROM bids b
                LEFT JOIN products p ON b.product_id = p.id
                WHERE {$whereClause}
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getTotalCount($filters = []) {
        $where = ["1=1"];
        $params = [];
        
        if (!empty($filters['product_id'])) {
            $where[] = "b.product_id = :product_id";
            $params['product_id'] = $filters['product_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "b.status = :status";
            $params['status'] = $filters['status'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) as total FROM bids b WHERE {$whereClause}";
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'];
    }
}
