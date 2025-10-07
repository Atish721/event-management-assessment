

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Event Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Event Portal Login</h2>
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-control" id="timezone" required>
                                    <option value="UTC">UTC</option>
                                    <option value="America/New_York">EST</option>
                                    <option value="America/Los_Angeles">PST</option>
                                    <option value="Europe/London">GMT</option>
                                    <option value="Asia/Kolkata">IST</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div id="errorAlert" class="alert alert-danger mt-3 d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const timezone = document.getElementById('timezone').value;

            try {
                const response = await axios.post('/api/login', {
                    username,
                    password,
                    timezone
                });

                localStorage.setItem('authToken', response.data.token);
                localStorage.setItem('loginToken', response.data.login_token);
                localStorage.setItem('user', JSON.stringify(response.data.user));
                
                window.location.href = '/admin/dashboard';
            } catch (error) {
                const errorAlert = document.getElementById('errorAlert');
                errorAlert.textContent = error.response?.data?.message || 'Login failed';
                errorAlert.classList.remove('d-none');
            }
        });
    </script>
</body>
</html>