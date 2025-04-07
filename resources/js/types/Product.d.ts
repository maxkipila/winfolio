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
    price?: Prices;
    prices?: Array<Prices>
    theme?: Theme
}