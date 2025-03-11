import React from 'react';
import { Link } from '@inertiajs/react';

export default function Pagination({ links, search }) {
    return (
        <nav>
            <ul className="pagination">
                {links.map((link, index) => (
                    <li key={index} className={`page-item ${link.active ? 'active' : ''}`}>
                        <Link
                            className="page-link"
                            href={link.url ? `${link.url}&search=${search}` : null}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    </li>
                ))}
            </ul>
        </nav>
    );
}
