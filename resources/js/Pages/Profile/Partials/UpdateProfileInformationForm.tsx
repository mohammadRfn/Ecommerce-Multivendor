import InputError from '@/Components/Core/InputError';
import InputLabel from '@/Components/Core/InputLabel';
import PrimaryButton from '@/Components/Core/PrimaryButton';
import TextInput from '@/Components/Core/TextInput';
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}

export default function UpdateProfileInformationForm({
    mustVerifyEmail,
    status,
    className = '',
}: Props) {
    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        name: '',
        email: '',
    });

    const updateProfile: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Profile Information
                </h2>

                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Update your profile information and email address.
                </p>
            </header>

            <form onSubmit={updateProfile} className="mt-6 space-y-6">
                <div>
                    <InputLabel htmlFor="name" value="Name" />
                    <TextInput
                        id="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        className="mt-1 block w-full"
                        autoComplete="name"
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <TextInput
                        id="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        className="mt-1 block w-full"
                        autoComplete="email"
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                {mustVerifyEmail && status === 'verification-link-sent' && (
                    <p className="text-sm text-green-600 dark:text-green-400">
                        A new verification link has been sent to your email address.
                    </p>
                )}

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Save</PrimaryButton>
                    {recentlySuccessful && (
                        <p className="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                    )}
                </div>
            </form>
        </section>
    );
}
