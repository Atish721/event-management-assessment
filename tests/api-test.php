<?php

class ApiTester {
    private $baseUrl = 'http://127.0.0.1:8000';
    private $token = '';
    private $loginToken = '';
    
    public function testAllApis() {
        echo "=== Event Management API Testing ===\n\n";
        
    
        $this->testLogin();
        
        if (!$this->token) {
            echo "❌ Login failed. Stopping tests.\n";
            return;
        }
        
  
        $this->testCheckAuth();
        
   
        $this->testCategoryApis();
        
   
        $this->testEventApis();
        
    
        $this->testLogout();
        
        echo "\n=== All Tests Completed ===\n";
    }
    
    private function testLogin() {
        echo "1. Testing Login API...\n";
        
        $data = [
            'username' => 'admin',
            'password' => 'admin123',
            'timezone' => 'Asia/Kolkata'
        ];
        
        $response = $this->makeRequest('/api/login', 'POST', $data);
        
        if (isset($response['user']) && isset($response['token'])) {
            $this->token = $response['token'];
            $this->loginToken = $response['login_token'];
            echo "✅ Login successful. Token received.\n";
        } else {
            echo "❌ Login failed: " . ($response['message'] ?? 'Unknown error') . "\n";
        }
    }
    
    private function testCheckAuth() {
        echo "2. Testing Check Auth API...\n";
        
        $response = $this->makeRequest('/api/check-auth', 'GET');
        
        if (isset($response['authenticated']) && $response['authenticated']) {
            echo "✅ Auth check successful.\n";
        } else {
            echo "❌ Auth check failed.\n";
        }
    }
    
    private function testCategoryApis() {
        echo "3. Testing Category APIs...\n";
        
    
        $response = $this->makeRequest('/api/categories', 'GET');
        if (isset($response['categories'])) {
            echo "   ✅ Get categories successful (" . count($response['categories']) . " categories)\n";
        } else {
            echo "   ❌ Get categories failed\n";
        }
        
    
        $response = $this->makeRequest('/api/categories/nested', 'GET');
        if (isset($response['categories'])) {
            echo "   ✅ Get nested categories successful\n";
        } else {
            echo "   ❌ Get nested categories failed\n";
        }
        

        $categoryData = [
            'name' => 'Test Category ' . time(),
            'parent_id' => null
        ];
        
        $response = $this->makeRequest('/api/categories', 'POST', $categoryData);
        if (isset($response['category'])) {
            $categoryId = $response['category']['id'];
            echo "   ✅ Create category successful (ID: $categoryId)\n";
            
          
            $this->testCategoryId = $categoryId;
        } else {
            echo "   ❌ Create category failed\n";
        }
    }
    
    private function testEventApis() {
        echo "4. Testing Event APIs...\n";
        
     
        $response = $this->makeRequest('/api/events', 'GET');
        if (isset($response['events'])) {
            echo "   ✅ Get events successful (" . count($response['events']) . " events)\n";
        } else {
            echo "   ❌ Get events failed\n";
        }
        
 
        $response = $this->makeRequest('/api/admin/events?filter=all', 'GET');
        if (isset($response['events'])) {
            echo "   ✅ Get admin events successful (" . count($response['events']) . " events)\n";
        } else {
            echo "   ❌ Get admin events failed\n";
        }
        
   
        $eventData = [
            'title' => 'API Test Event ' . time(),
            'description' => 'This event was created via API testing',
            'category_id' => $this->testCategoryId ?? 1,
            'publish_date' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ];
        
        $response = $this->makeRequest('/api/events', 'POST', $eventData);
        if (isset($response['event'])) {
            $eventId = $response['event']['id'];
            echo "   ✅ Create event successful (ID: $eventId)\n";
            
        
            $this->testEventId = $eventId;
        } else {
            echo "   ❌ Create event failed: " . ($response['message'] ?? 'Unknown error') . "\n";
            if (isset($response['errors'])) {
                print_r($response['errors']);
            }
        }
        

        if (isset($this->testEventId)) {
            $response = $this->makeRequest("/api/events/{$this->testEventId}", 'DELETE');
            if (isset($response['message']) && $response['message'] === 'Event deleted successfully') {
                echo "   ✅ Delete event successful\n";
            } else {
                echo "   ❌ Delete event failed\n";
            }
        }
    }
    
    private function testLogout() {
        echo "5. Testing Logout API...\n";
        
        $response = $this->makeRequest('/api/logout', 'POST');
        
        if (isset($response['message']) && $response['message'] === 'Logged out successfully') {
            echo "✅ Logout successful.\n";
        } else {
            echo "❌ Logout failed.\n";
        }
    }
    
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $headers = [
            'Content-Type: application/json',
        ];
        
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
            $headers[] = 'X-Login-Token: ' . $this->loginToken;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}


$tester = new ApiTester();
$tester->testAllApis();