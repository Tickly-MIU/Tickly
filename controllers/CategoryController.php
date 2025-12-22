<?php
require_once __DIR__ . '/../core/Controller.php';

class CategoryController extends Controller
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = $this->model("Category");
    }

    // POST: /api/category/create
    public function create($data = [])
    {
        if (empty($data['category_name']) || empty($data['user_id'])) {
            return $this->jsonResponse(['message' => 'Category name and user ID required'], 400);
        }

        $created = $this->categoryModel->create([
            'category_name' => $data['category_name'],
            'user_id'       => $data['user_id']
        ]);

        if ($created) {
            $this->logActivity($data['user_id'], "Created category: " . $data['category_name']);
            return $this->jsonResponse(['message' => 'Category created successfully']);
        }

        return $this->jsonResponse(['message' => 'Failed to create category'], 500);
    }

    // POST: /api/category/read
    public function read($data = [])
    {
        if (!empty($data['category_id'])) {
            $category = $this->categoryModel->getById($data);
            if ($category) {
                return $this->jsonResponse($category);
            }
            return $this->jsonResponse(['message' => 'Category not found'], 404);
        } else {
            $categories = $this->categoryModel->getAll();
            return $this->jsonResponse($categories);
        }
    }

    // POST: /api/category/update
    public function update($data = [])
    {
        if (empty($data['category_id']) || empty($data['category_name'])) {
            $this->logActivity($data['user_id'], "Updated category ID {$data['category_id']} to name: {$data['category_name']}");
            return $this->jsonResponse(['message' => 'Category ID and name required'], 400);
        }

        $updated = $this->categoryModel->update($data);
        if ($updated) {
            $this->logActivity($data['user_id'], "Updated category ID {$data['category_id']} to name: {$data['category_name']}");
            return $this->jsonResponse(['message' => 'Category updated successfully']);
        }

        return $this->jsonResponse(['message' => 'Failed to update category'], 500);
    }

    // POST: /api/category/delete
    public function delete($data = [])
    {
        if (empty($data['category_id'])) {
            return $this->jsonResponse(['message' => 'Category ID required'], 400);
        }

        $deleted = $this->categoryModel->delete($data);
        if ($deleted) {
            $this->logActivity($data['user_id'], "Deleted category ID: {$data['category_id']}");
            return $this->jsonResponse(['message' => 'Category deleted successfully']);
        }

        return $this->jsonResponse(['message' => 'Failed to delete category'], 500);
    }
}
