<?php

class CategoryControllerTest extends BaseTestCase
{
    private $categoryController;
    private $mockCategoryModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockCategoryModel = $this->createMock(Category::class);
        $this->categoryController = new CategoryController();

        // Use reflection to inject the mock
        $reflection = new ReflectionClass($this->categoryController);
        $property = $reflection->getProperty('categoryModel');
        $property->setAccessible(true);
        $property->setValue($this->categoryController, $this->mockCategoryModel);
    }

    public function testGetAllCategories()
    {
        $categories = [
            ['id' => 1, 'name' => 'Work', 'user_id' => 1],
            ['id' => 2, 'name' => 'Personal', 'user_id' => 1]
        ];

        $this->mockCategoryModel->expects($this->once())
            ->method('getAll')
            ->willReturn($categories);

        ob_start();
        $this->categoryController->read([]);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals($categories, $response['data']);
    }

    public function testCreateCategory()
    {
        $categoryData = [
            'category_name' => 'New Category',
            'user_id' => 1
        ];

        $this->mockCategoryModel->expects($this->once())
            ->method('create')
            ->with($this->callback(function($data) {
                return $data['category_name'] === 'New Category' && $data['user_id'] == 1;
            }))
            ->willReturn(true);

        ob_start();
        $this->categoryController->create($categoryData);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Category created successfully', $response['message']);
    }

    public function testUpdateCategory()
    {
        $categoryData = [
            'category_id' => 1,
            'category_name' => 'Updated Category'
        ];

        $this->mockCategoryModel->expects($this->once())
            ->method('update')
            ->with($this->callback(function($data) {
                return $data['category_name'] === 'Updated Category' && $data['category_id'] == 1;
            }))
            ->willReturn(true);

        ob_start();
        $this->categoryController->update($categoryData);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Category updated successfully', $response['message']);
    }

    public function testDeleteCategory()
    {
        $categoryData = ['category_id' => 1];

        $this->mockCategoryModel->expects($this->once())
            ->method('delete')
            ->with($this->callback(function($data) {
                return $data['category_id'] == 1;
            }))
            ->willReturn(true);

        ob_start();
        $this->categoryController->delete($categoryData);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('Category deleted successfully', $response['message']);
    }
}