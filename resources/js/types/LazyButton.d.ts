interface LazyButton {
    only: Array<string>;
    preserveScroll: boolean;
    preserveState: boolean;
    method: "post" | "get";
    data: {
        page?: number;
        paginate?: number;
        sort?: Array<{ name: string; order: "ASC" | "DESC" }>;
    };
    as: "button";
    className: string | undefined;
    href: string;
}
