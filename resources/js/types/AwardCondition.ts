interface AwardCondition {
    award: Award;
    award_id: number;
    condition: string;
    condition_id: number;
    condition_name: string;
    product: ProductLego;
    product_id: number;
    product_name: string;
    category: string;
    category_id: number;
    category_name: string;
    required_count: number;
    required_value: number;
    required_percentage: number;
    created_at: string;
    updated_at: string;
}
