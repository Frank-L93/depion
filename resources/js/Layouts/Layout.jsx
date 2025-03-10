import { usePage } from '@inertiajs/react';
import Navbar from '../Pages/Generic/navbar';
export default function Layout({ children }) {
    const props = usePage().props;

    return (
    <>
    <Navbar />
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                {children}
            </div>
        </div>
    </div>
    </>
    );
}
