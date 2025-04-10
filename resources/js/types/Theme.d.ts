interface Theme {
    id:number,
    name: string,
    parent?: Theme,
    children?: Array<Theme>
}