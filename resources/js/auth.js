import api from './api.js';

export const auth = {
    // Login user
    async login(email, password) {
        try {
            const response = await api.post('/api/login', { email, password });
            localStorage.setItem('auth_token', response.data.token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            return response.data;
        } catch (error) {
            throw error.response?.data?.message || 'Login failed';
        }
    },

    // Logout user
    async logout() {
        try {
            await api.post('/api/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
        }
    },

    // Get current user
    async getUser() {
        try {
            const response = await api.get('/api/user');
            return response.data;
        } catch (error) {
            return null;
        }
    },

    // Check if user is authenticated
    isAuthenticated() {
        return !!localStorage.getItem('auth_token');
    },

    // Get stored user
    getStoredUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    }
};
