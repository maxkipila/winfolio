interface Award {
    id: number;
    name: string;
    description: string;
    icon?: string;
    created_at: string;
    updated_at: string;
    category: string;
    condition_type: ConditionType;
    product_id: number;
    category_id: number;
    required_count: number;
    required_value: number;
    required_percentage: number;
}
