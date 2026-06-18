const API_BASE = 'http://localhost/eduka/backend/api';

function obtenerToken() {
    return localStorage.getItem('eduka_token');
}

function guardarToken(token) {
    localStorage.setItem('eduka_token', token);
}

function eliminarToken() {
    localStorage.removeItem('eduka_token');
}

function estaAutenticado() {
    return obtenerToken() !== null;
}

async function apiFetch(endpoint, options = {}) {
    const token = obtenerToken();

    const headers = {
        'Content-Type': 'application/json',
        ...options.headers
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_BASE}${endpoint}`, {
        ...options,
        headers
    });

    const data = await response.json();

    // Solo redirigir automáticamente si NO es el propio endpoint de login
    // (un 401 en login significa "credenciales incorrectas", no "sesión inválida")
    const esLogin = endpoint.includes('/auth/login.php');

    if (response.status === 401 && !esLogin) {
        eliminarToken();
        window.location.href = '/eduka/frontend/pages/login.html';
        return { status: response.status, data };
    }

    return { status: response.status, data };
}

async function apiGet(endpoint) {
    return apiFetch(endpoint, { method: 'GET' });
}

async function apiPost(endpoint, body) {
    return apiFetch(endpoint, { method: 'POST', body: JSON.stringify(body) });
}

async function apiPut(endpoint, body) {
    return apiFetch(endpoint, { method: 'PUT', body: JSON.stringify(body) });
}

async function apiDelete(endpoint) {
    return apiFetch(endpoint, { method: 'DELETE' });
}