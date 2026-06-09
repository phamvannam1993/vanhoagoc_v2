import { defineStore } from 'pinia';

export const useUserStore = defineStore('user', {
    state: () => ({
        user: null, // Thông tin người dùng
        isAuthenticated: false,
    }),
    actions: {
        setUser(user) {
            this.user = user;
            this.isAuthenticated = !!user;
        },
        logout() {
            this.user = null;
            this.isAuthenticated = false;
        },
    },
});
