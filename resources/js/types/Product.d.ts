interface Product {
    id: number;
    // set_num: string;
    product_num: string;
    name: string;
    year: number;
    num_parts: number;
    theme_id: number;
    thumbnail: string;
    img_url: string;
    latest_price?: Prices;
    prices?: Array<Prices>
    theme?: Theme
    reviews?: Array<Review>
    favourited: boolean
    sets?: Array<Product>
    minifigs?: Array<Product>
    annual_growth: number,
    monthly_growth: number,
    weekly_growth: number,
    growth: {
        weekly: number,
        monthly: number,
        yearly: number,
        annual: number
    }
}