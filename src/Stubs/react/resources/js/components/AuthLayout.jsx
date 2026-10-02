import React from 'react';

export default function AuthLayout({ title, subtitle, children, footer }) {
    return (
        <main className="auth-container">
            <div className="auth-logo-area">
                <img src="/logo.png" alt="Slenix Veil" width={40} height={40} />
                <span>Slenix</span>
            </div>
            <div className="card">
                <div className="card-header">
                    <h1 className="card-title">{title}</h1>
                    {subtitle && <p className="card-subtitle">{subtitle}</p>}
                </div>
                {children}
                {footer && <div className="card-footer-center">{footer}</div>}
            </div>
        </main>
    );
}