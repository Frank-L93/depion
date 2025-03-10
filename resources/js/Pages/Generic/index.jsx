import { Head } from '@inertiajs/react';
import Layout from '@/Layouts/Layout';

export default function index() {
    return (
        <Layout>
                    <Head title="Welcome" />
                        <div className="text-2xl font-semibold mx-auto text-purple-300 text-center">
                            Hi!
                        </div>


              </Layout>
    );
}
