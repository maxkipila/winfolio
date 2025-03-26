interface SetLego extends Resource {
    id: number;
    set_num: string;
    name: string;
    year: number;
    num_parts: number;
    theme_id: number;
    thumbnail: string;
    img_url: string;
    release_date: string;
    availability: string;
    packaging: string;
    pieces: number;
    minifigs_count: number;
    retail_price: number;
    market_price: number;
    product_num: string;
    forecast: string;
    theme: Theme;
}
