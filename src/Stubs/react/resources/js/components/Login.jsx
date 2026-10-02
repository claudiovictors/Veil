import React, { useState } from 'react';
import { api } from '../http';
import AuthLayout from './AuthLayout';
import Input from './Input';
import Button from './Button';

export default function Login({ loginUrl, registerUrl }) {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState('');
    const [loading, setLoading] = useState(false);

    async function handleSubmit(e) {
        e.preventDefault();
        setErrors({});
        setMessage('');
        setLoading(true);

        try {
            const data = await api(loginUrl, {
                method: 'POST',
                body: { email, password },
            });
            window.location.href = data.redirect || '/dashboard';
        } catch (err) {
            if (err.status === 422 && err.data?.errors) {
                const next = {};
                Object.keys(err.data.errors).forEach((k) => {
                    next[k] = Array.isArray(err.data.errors[k])
                        ? err.data.errors[k][0]
                        : err.data.errors[k];
                });
                setErrors(next);
            } else {
                setMessage(err.data?.message || err.message || 'Invalid credentials.');
            }
        } finally {
            setLoading(false);
        }
    }

    return (
        <AuthLayout
            title="Sign in"
            subtitle="Enter your credentials to continue"
            footer={
                <a href={registerUrl || '/register'} className="link-sub">
                    Don't have an account? Register
                </a>
            }
        >
            {message && (
                <div className="alert alert-error" style={{ marginBottom: 16 }}>
                    <i className="bx bx-error-circle" />
                    <span>{message}</span>
                </div>
            )}
            <form onSubmit={handleSubmit} noValidate>
                <Input
                    name="email"
                    type="email"
                    label="Email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    error={errors.email}
                    placeholder="you@example.com"
                    autoComplete="email"
                    required
                />
                <Input
                    name="password"
                    type="password"
                    label="Password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    error={errors.password}
                    placeholder="••••••••"
                    autoComplete="current-password"
                    required
                />
                <Button full loading={loading}>Sign in</Button>
            </form>
        </AuthLayout>
    );
}