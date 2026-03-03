import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role: string;
    user_type: string;
    avatar_url: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash: {
        message: string | null;
        error: string | null;
    };
    appName: string;
    appLogo: string | null;
    ziggy: Config & { location: string };
};
