interface News {
    id: number;
    title: string;
    content: string;
    image_url: string;
    category: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    status: string;
}
