interface User extends Resource {
    id: number;
    first_name: string;
    last_name: string;
    nickname: string;
    prefix: string;
    phone: string;
    street: string;
    street_2: string;
    city: string;
    psc: string;
    country: string;
    email: string;
    email_verified_at?: string;
    status: number;
    user: User;
    thumbnail: string;
    subscriptions: Array<Subscription>;
    day: number;
    month: number;
    year: number;
    created_at: string;
    updated_at: string;
    products: Array<Product>
    favourites: Array<Favourite>
}
