import React, { useState, useEffect } from 'react';
import { api } from '../http';

export default function AppLayout({ user, logoutUrl, children }) {
    const [theme, setThemeState] = useState(() => localStorage.getItem('theme') || 'light');
    const [menuOpen, setMenuOpen] = useState(false);
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [loggingOut, setLoggingOut] = useState(false);

    useEffect(() => {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    }, [theme]);

    useEffect(() => {
        const onDocClick = (e) => {
            if (!e.target.closest('#userPillContainer')) {
                setMenuOpen(false);
            }
        };
        document.addEventListener('click', onDocClick);
        return () => document.removeEventListener('click', onDocClick);
    }, []);

    async function handleLogout(e) {
        e.preventDefault();
        if (loggingOut) return;
        setLoggingOut(true);
        try {
            const data = await api(logoutUrl, { method: 'POST' });
            window.location.href = data?.redirect || '/login';
        } catch {
            window.location.href = '/login';
        }
    }

    const initial = (user?.name || 'U').charAt(0).toUpperCase();

    return (
        <div className="fi-layout">
            <aside className={`fi-sidebar${sidebarOpen ? ' show' : ''}`} id="sidebar">
                <header className="fi-sidebar-header">
                    <a href="/dashboard" className="fi-logo">
                        <img src="/logo.png" alt="Slenix Veil" width={35} height={35} />
                        <span className="fi-logo-text">Slenix Veil</span>
                    </a>
                </header>
                <nav className="fi-sidebar-nav">
                    <ul className="fi-nav-list">
                        <li className="fi-nav-item">
                            <a href="/dashboard" className="fi-nav-link active">
                                <i className="bx bx-home fi-nav-icon" />
                                <span className="fi-nav-label">Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </aside>

            <div className="fi-main-wrapper">
                <header className="fi-topbar">
                    <div className="fi-topbar-left">
                        <button
                            type="button"
                            className="fi-mobile-menu-btn"
                            onClick={() => setSidebarOpen((v) => !v)}
                        >
                            <i className="bx bx-menu" />
                        </button>
                    </div>
                    <div className="fi-topbar-right">
                        <div className="fi-user-menu" id="userPillContainer">
                            <button
                                type="button"
                                className="fi-user-button"
                                aria-expanded={menuOpen}
                                onClick={(e) => {
                                    e.stopPropagation();
                                    setMenuOpen((v) => !v);
                                }}
                            >
                                <div className="fi-avatar">{initial}</div>
                                <span className="fi-user-name">{user?.name || 'admin'}</span>
                            </button>
                            <div className={`fi-dropdown${menuOpen ? ' show' : ''}`}>
                                <div className="fi-dropdown-header">
                                    <span className="fi-dropdown-name">{user?.name || 'admin'}</span>
                                </div>
                                <div className="fi-dropdown-divider" />
                                <div className="fi-dropdown-appearance">
                                    <span className="fi-dropdown-label">Toggle system theme</span>
                                    <div className="fi-theme-toggles">
                                        <button
                                            type="button"
                                            className={`fi-theme-btn${theme === 'light' ? ' active' : ''}`}
                                            onClick={() => setThemeState('light')}
                                        >
                                            <i className="bx bx-sun" />
                                        </button>
                                        <button
                                            type="button"
                                            className={`fi-theme-btn${theme === 'dark' ? ' active' : ''}`}
                                            onClick={() => setThemeState('dark')}
                                        >
                                            <i className="bx bx-moon" />
                                        </button>
                                    </div>
                                </div>
                                <div className="fi-dropdown-divider" />
                                <button
                                    type="button"
                                    className="fi-dropdown-item"
                                    onClick={handleLogout}
                                    disabled={loggingOut}
                                >
                                    <i className="bx bx-log-out" /> Sign out
                                </button>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="fi-main">
                    <div className="fi-main-content">{children}</div>
                </main>
            </div>

            <div
                className={`fi-mobile-overlay${sidebarOpen ? ' show' : ''}`}
                onClick={() => setSidebarOpen(false)}
            />
        </div>
    );
}