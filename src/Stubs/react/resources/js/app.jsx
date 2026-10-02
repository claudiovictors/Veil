import React from 'react';
import { createRoot } from 'react-dom/client';
import '../css/veil.css';
import Login from './components/Login';
import Register from './components/Register';
import Dashboard from './components/Dashboard';

const pages = {
    login: Login,
    register: Register,
    dashboard: Dashboard,
};

const el = document.getElementById('app');
if (el) {
    const page = el.dataset.page || 'login';
    let props = {};
    try {
        props = JSON.parse(el.dataset.props || '{}');
    } catch {
        props = {};
    }
    const Component = pages[page] || Login;
    createRoot(el).render(<Component {...props} />);
}