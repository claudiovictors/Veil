import React from 'react';
import AppLayout from './AppLayout';

export default function Dashboard({ user, logoutUrl }) {
    const initial = (user?.name || 'U').charAt(0).toUpperCase();

    return (
        <AppLayout user={user} logoutUrl={logoutUrl}>
            <div className="fi-page-header">
                <h1 className="fi-page-title">Dashboard</h1>
            </div>
            <div className="fi-widgets-grid">
                <div className="fi-card">
                    <div className="fi-card-content">
                        <div className="fi-card-avatar">{initial}</div>
                        <div className="fi-card-user-info">
                            <div className="fi-welcome-title">
                                Welcome, {user?.name || 'User'}
                            </div>
                            <div className="fi-welcome-role">{user?.email || ''}</div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}