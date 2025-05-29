interface Product {
    id: number;
    // set_num: string;
    product_num: string;
    product_type: string;
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
    availability: any,
    user_owns?: Array<{
        condition: string,
        currency: string,
        purchase_day: string,
        purchase_month: string,
        purchase_year: string,
        purchase_price: number
    }>
    growth: {
        weekly: number,
        monthly: number,
        yearly: number,
        annual: number
    }
}