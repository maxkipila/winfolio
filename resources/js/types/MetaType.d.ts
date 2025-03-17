interface MetaType {
    current_page: number;
    last_page: number;
    per_page: number;
    from: number;
    to: number;
    total: number;
    next?: number | any;
    links?: Array<{ url: string; label: string; active: boolean }>;
}
