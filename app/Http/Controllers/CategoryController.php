<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index()
    {
        
        
        try {
            
            $mainCategories = DB::select('
                select id, name, parent_id 
                from categories 
                where parent_id is null 
                order by name
            ');

            $categories = [];

            foreach ($mainCategories as $category) {
              
                
                $children = DB::select('
                    select id, name, parent_id 
                    from categories 
                    where parent_id = ? 
                    order by name
                ', [$category->id]);
                
                $nestedName = $this->getNestedName($category->id);

                $categories[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'nested_name' => $nestedName,
                    'parent_id' => $category->parent_id,
                    'children' => $children
                ];
            }

          
            
            return response()->json(['categories' => $categories]);

        } catch (\Exception $e) {
         
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function nested()
    {
      
        
        
        try {
            
            $mainCategories = DB::select('
                select id, name, parent_id 
                from categories 
                where parent_id is null 
                order by name
            ');

            $categories = [];

            foreach ($mainCategories as $mainCategory) {
                
                $children = DB::select('
                    select id, name, parent_id 
                    from categories 
                    where parent_id = ? 
                    order by name
                ', [$mainCategory->id]);

                $categoryWithChildren = [
                    'id' => $mainCategory->id,
                    'name' => $mainCategory->name,
                    'parent_id' => $mainCategory->parent_id,
                    'children' => []
                ];

                
                foreach ($children as $child) {
                    $grandchildren = DB::select('
                        select id, name, parent_id 
                        from categories 
                        where parent_id = ? 
                        order by name
                    ', [$child->id]);

                    $categoryWithChildren['children'][] = [
                        'id' => $child->id,
                        'name' => $child->name,
                        'parent_id' => $child->parent_id,
                        'children' => $grandchildren
                    ];
                }

                $categories[] = $categoryWithChildren;
            }

        
            
            return response()->json(['categories' => $categories]);

        } catch (\Exception $e) {
          
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function store(Request $request)
    {
      
        
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        try {
            
            if ($request->parent_id) {
                $parentExists = DB::selectOne('SELECT id FROM categories WHERE id = ?', [$request->parent_id]);
                if (!$parentExists) {
                    throw ValidationException::withMessages([
                        'parent_id' => ['The selected parent category does not exist.'],
                    ]);
                }
            }

            
            DB::insert('
                insert into categories (name, parent_id, created_at, updated_at) 
                values (?, ?, ?, ?)
            ', [
                $request->name,
                $request->parent_id,
                now(),
                now()
            ]);

            $categoryId = DB::getPdo()->lastInsertId();

            
            $category = DB::selectOne('
                select id, name, parent_id, created_at, updated_at 
                from categories 
                where id = ?
            ', [$categoryId]);

           
            
            return response()->json([
                'message' => 'Category created successfully',
                'category' => $category
            ], 201);

        } catch (\Exception $e) {
          
            
            return response()->json(['error' => 'Failed to create category'], 500);
        }
    }


     public function show($id)
    {
       
        
        
        try {
            $category = DB::selectOne('
                select id, name, parent_id, created_at, updated_at 
                from categories 
                where id = ?
            ', [$id]);

            if (!$category) {
                return response()->json(['message' => 'Category not found'], 404);
            }

            
            if ($category->parent_id) {
                $parent = DB::selectOne('select id, name from categories where id = ?', [$category->parent_id]);
                $category->parent = $parent;
            }

            
            $children = DB::select('
                select id, name 
                from categories 
                where parent_id = ? 
                order by name
            ', [$id]);

            $category->children = $children;

            return response()->json(['category' => $category]);

        } catch (\Exception $e) {
           
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }



    
    private function getNestedName($categoryId)
    {
        $nameParts = [];
        $currentId = $categoryId;

        while ($currentId) {
            $category = DB::selectOne('select id, name, parent_id from categories where id = ?', [$currentId]);
            
            if ($category) {
                array_unshift($nameParts, $category->name);
                $currentId = $category->parent_id;
            } else {
                break;
            }
        }

        return implode(' > ', $nameParts);
    }
}