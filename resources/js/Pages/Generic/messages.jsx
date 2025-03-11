import React from 'react';
import { usePage } from '@inertiajs/react';

export default function Messages() {
    const { props } = usePage();
    const { success, errors, error } = props;

    return (
        <div>
            {success && (
                <div className="alert alert-success">
                    {success}
                </div>
            )}

            {errors && errors.activation_response && (
                <div className="alert alert-danger" role="alert">
                    {errors.activation_response}
                </div>
            )}

            {error && (
                <div className="alert alert-danger">
                    {error}
                </div>
            )}
        </div>
    );
}
