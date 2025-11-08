<?php
/**
 * Product Model
 */

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll($filters = [], $limit = PRODUCTS_PER_PAGE, $offset = 0) {
        $where = ["p.status = 'active'"];
        $params = [];
        
        if (!empty($filters['category'])) {
            $where[] = "c.slug = :category";
            $params['category'] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search OR p.description LIKE :search OR p.artist LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['min_price'])) {
            $where[] = "p.current_bid >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $where[] = "p.current_bid <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }
        
        if (!empty($filters['hair_color'])) {
            $where[] = "p.hair_color = :hair_color";
            $params['hair_color'] = $filters['hair_color'];
        }
        
        if (!empty($filters['eye_color'])) {
            $where[] = "p.eye_color = :eye_color";
            $params['eye_color'] = $filters['eye_color'];
        }
        
        if (!empty($filters['size'])) {
            $where[] = "p.size = :size";
            $params['size'] = $filters['size'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $orderBy = "p.featured DESC, p.created_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $orderBy = "COALESCE(p.current_bid, p.base_price) ASC";
                    break;
                case 'price_high':
                    $orderBy = "COALESCE(p.current_bid, p.base_price) DESC";
                    break;
                case 'newest':
                    $orderBy = "p.created_at DESC";
                    break;
                case 'oldest':
                    $orderBy = "p.created_at ASC";
                    break;
                case 'popular':
                    $orderBy = "p.views DESC";
                    break;
            }
        }
        
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       (SELECT image_path FROM product_images 
                        WHERE product_id = p.id AND is_primary = 1 
                        LIMIT 1) as primary_image,
                       (SELECT COUNT(*) FROM bids 
                        WHERE product_id = p.id AND status = 'confirmed') as bid_count
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = :id";
        
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    public function getBySlug($slug) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = :slug";
        
        return $this->db->fetchOne($sql, ['slug' => $slug]);
    }
    
    public function getImages($productId) {
        $sql = "SELECT * FROM product_images 
                WHERE product_id = :product_id 
                ORDER BY is_primary DESC, display_order ASC";
        
        return $this->db->fetchAll($sql, ['product_id' => $productId]);
    }
    
    public function getFeatured($limit = 6) {
        $sql = "SELECT p.*, c.name as category_name,
                       (SELECT image_path FROM product_images 
                        WHERE product_id = p.id AND is_primary = 1 
                        LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND p.featured = 1
                ORDER BY p.created_at DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['limit' => $limit]);
    }
    
    public function getRelated($productId, $categoryId, $limit = 4) {
        $sql = "SELECT p.*, c.name as category_name,
                       (SELECT image_path FROM product_images 
                        WHERE product_id = p.id AND is_primary = 1 
                        LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' 
                AND p.category_id = :category_id 
                AND p.id != :product_id
                ORDER BY RAND()
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            'category_id' => $categoryId,
            'product_id' => $productId,
            'limit' => $limit
        ]);
    }
    
    public function getTotalCount($filters = []) {
        $where = ["p.status = 'active'"];
        $params = [];
        
        if (!empty($filters['category'])) {
            $where[] = "c.slug = :category";
            $params['category'] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(p.name LIKE :search OR p.description LIKE :search OR p.artist LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['min_price'])) {
            $where[] = "p.current_bid >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $where[] = "p.current_bid <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) as total
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE {$whereClause}";
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'];
    }
    
    public function incrementViews($id) {
        $sql = "UPDATE products SET views = views + 1 WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
    
    public function updateCurrentBid($id, $amount) {
        $sql = "UPDATE products SET current_bid = :amount WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'amount' => $amount]);
    }
    
    public function create($data) {
        return $this->db->insert('products', $data);
    }
    
    public function update($id, $data) {
        return $this->db->update('products', $data, 'id = :id', ['id' => $id]);
    }
    
    public function delete($id) {
        return $this->db->delete('products', 'id = :id', ['id' => $id]);
    }
    
    public function addImage($productId, $imagePath, $isPrimary = false) {
        $data = [
            'product_id' => $productId,
            'image_path' => $imagePath,
            'is_primary' => $isPrimary ? 1 : 0
        ];
        
        if ($isPrimary) {
            // Remove primary flag from other images
            $this->db->query(
                "UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id",
                ['product_id' => $productId]
            );
        }
        
        return $this->db->insert('product_images', $data);
    }
    
    public function deleteImage($imageId) {
        return $this->db->delete('product_images', 'id = :id', ['id' => $imageId]);
    }
    
    public function getFilterOptions() {
        return [
            'hair_colors' => $this->db->fetchAll(
                "SELECT DISTINCT hair_color FROM products 
                 WHERE hair_color IS NOT NULL AND hair_color != '' 
                 AND status = 'active' 
                 ORDER BY hair_color"
            ),
            'eye_colors' => $this->db->fetchAll(
                "SELECT DISTINCT eye_color FROM products 
                 WHERE eye_color IS NOT NULL AND eye_color != '' 
                 AND status = 'active' 
                 ORDER BY eye_color"
            ),
            'sizes' => $this->db->fetchAll(
                "SELECT DISTINCT size FROM products 
                 WHERE size IS NOT NULL AND size != '' 
                 AND status = 'active' 
                 ORDER BY size"
            )
        ];
    }
}
