import { usePage } from '@inertiajs/react';
import Navbar from '../Pages/Generic/navbar';
import Messages from '../Pages/Generic/messages';
export default function Layout({ children }) {
    const props = usePage().props;

    return (
    <>
    <Navbar />
    <div className="container">
        <div className="row justify-content-center">
            <Messages />
            <div className="col-md-8">
                {children}
            </div>
        </div>
    </div>
    </>
    );
}
