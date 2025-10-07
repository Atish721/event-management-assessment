

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Event Portal</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3" id="userInfo"></span>
                <button class="btn btn-outline-light btn-sm" onclick="logout()">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @yield('content')
    </div>

    
    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Session Ended</h5>
                </div>
                <div class="modal-body">
                    You have logged in from another browser. You have been logged out from this session.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="redirectToLogin()">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        let authToken = localStorage.getItem('authToken');
        let loginToken = localStorage.getItem('loginToken');

 
        
        axios.interceptors.request.use(config => {
            if (authToken) {
                config.headers.Authorization = `Bearer ${authToken}`;
                config.headers['X-Login-Token'] = loginToken;
            }
            return config;
        });

        
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 401) {
                    showLogoutModal();
                }
                return Promise.reject(error);
            }
        );

        function showLogoutModal() {
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        }

        function redirectToLogin() {
            localStorage.removeItem('authToken');
            localStorage.removeItem('loginToken');
            window.location.href = '/login';
        }

        function logout() {
            axios.post('/api/logout').finally(() => {
                redirectToLogin();
            });
        }

        
        setInterval(() => {
            if (authToken) {
                axios.get('/api/check-auth').catch(error => {
                    if (error.response?.status === 401) {
                        showLogoutModal();
                    }
                });
            }
        }, 30000); 
    </script>
    @yield('scripts')
</body>
</html>