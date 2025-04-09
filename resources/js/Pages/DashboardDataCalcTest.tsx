import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Product } from '@/types';

interface TrendingProduct {
    product: Product;
    weekly_growth: number;
    annual_growth: number;
}

interface PageProps {
    auth: { user: any };
    trendingProducts: TrendingProduct[];
    topMovers: TrendingProduct[];
    portfolioValue: number;
}

export default function DashboardDataTestCalc({ auth, trendingProducts, topMovers, portfolioValue }: PageProps) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>}
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Hodnota portfolia */}
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h2 className="text-xl font-semibold mb-4">Hodnota portfolia</h2>
                            <div className="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                                <div className="flex-1 bg-gray-100 dark:bg-gray-700 p-6 rounded-lg">
                                    <div className="text-2xl md:text-4xl font-bold">${portfolioValue.toFixed(2)}</div>
                                    <div className="text-green-500 font-semibold mt-2">
                                        <span className="inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fillRule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                            </svg>
                                            +4.1 %
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Momentálně trendují */}
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <div className="flex justify-between items-center mb-4">
                                <h2 className="text-xl font-semibold">Momentálně trendují</h2>
                                <span className="text-sm text-gray-500">Položky, které si lidé nejčastěji přidávají do portfolia</span>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                {trendingProducts.map((item, index) => (
                                    <ProductCard
                                        key={index}
                                        product={item.product}
                                        weeklyGrowth={item.weekly_growth}
                                        annualGrowth={item.annual_growth}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Top Movers */}
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <div className="flex justify-between items-center mb-4">
                                <h2 className="text-xl font-semibold">Top Movers</h2>
                                <span className="text-sm text-gray-500">Položky s největšími cenovými změnami</span>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                {topMovers.map((item, index) => (
                                    <ProductCard
                                        key={index}
                                        product={item.product}
                                        weeklyGrowth={item.weekly_growth}
                                        annualGrowth={item.annual_growth}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Zobrazit další tlačítko */}
                    <div className="flex justify-center mb-6">
                        <button type="button" className="bg-yellow-500 hover:bg-yellow-600 text-black font-semibold py-2 px-6 rounded">
                            Zobrazit další
                        </button>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

interface ProductCardProps {
    product: Product;
    weeklyGrowth: number;
    annualGrowth: number;
}

const ProductCard = ({ product, weeklyGrowth, annualGrowth }: ProductCardProps) => {
    return (
        <div className="border dark:border-gray-700 rounded-lg overflow-hidden">
            <div className="p-4">
                <div className="flex items-start space-x-4">
                    <div className="flex-shrink-0 w-16 h-16">
                        {product.img_url ? (
                            <img src={product.img_url} alt={product.name} className="w-16 h-16 object-contain" />
                        ) : (
                            <div className="w-16 h-16 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <span className="text-gray-500 dark:text-gray-400">Žádný obrázek</span>
                            </div>
                        )}
                    </div>
                    <div>
                        <div className="text-sm text-gray-500">{product.product_num} {product.theme?.name || ''}</div>
                        <div className="font-semibold truncate">{product.name}</div>
                        <div className="text-sm">{product.year}</div>
                    </div>
                </div>

                <div className="mt-4 grid grid-cols-2 gap-4">
                    <div>
                        <div className="text-xs text-gray-500">Retail</div>
                        <div>${product.latest_price?.retail.toFixed(2) || '0.00'}</div>
                    </div>
                    <div>
                        <div className="text-xs text-gray-500">Value</div>
                        <div>${product.latest_price?.value.toFixed(2) || '0.00'}</div>
                    </div>
                    <div>
                        <div className="text-xs text-gray-500">Growth</div>
                        <div className={weeklyGrowth > 0 ? 'text-green-500' : 'text-red-500'}>
                            <span className="inline-flex items-center">
                                {weeklyGrowth > 0 ? (
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                    </svg>
                                ) : (
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                    </svg>
                                )}
                                {weeklyGrowth} %
                            </span>
                        </div>
                    </div>
                    <div>
                        <div className="text-xs text-gray-500">Annual</div>
                        <div className={annualGrowth > 0 ? 'text-green-500' : 'text-red-500'}>
                            {annualGrowth} %
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};