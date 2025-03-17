interface User extends Resource {
    id: number;
    name: string;
    first_name: string;
    last_name: string;
    phone: string;
    email: string;
    email_verified_at?: string;
    status: number;
    user: User;
    thumbnail: string;
}
