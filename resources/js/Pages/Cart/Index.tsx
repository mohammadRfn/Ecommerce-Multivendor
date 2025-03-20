import CartItem from "@/Components/App/CartItem";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import PrimaryButton from "@/Components/Core/PrimaryButton";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { GroupedCartItem, PageProps } from "@/types";
import { CreditCardIcon } from "@heroicons/react/24/outline";
import { Head, Link } from "@inertiajs/react";

function Index({
    csrf_token,
    cartItems,
    totalQuantity,
    totalPrice,
}: PageProps<{ cartItems: Record<number, GroupedCartItem> }>) {
    return (
        <AuthenticatedLayout>
            <Head title="Your Cart" />
            <div className="container mx-auto p-8 flex flex-col lg:flex-row gap-4">
                <div className="card flex-1 bg-white dark:bg-gray-800 order-2 lg:order-1">
                    <div className="card-body">
                        <h2>Shopping Cart</h2>
                        <div className="my-4">
                            {Object.keys(cartItems).length === 0 && (
                                <div className="py-2 text-gray-500 text-center">
                                    You don't have any Items yet.
                                </div>
                            )}
                            {Object.values(cartItems).map(cartItems => (
                                <div key={cartItems.user.id}>
                                <div className={"flex items-center justify-between pb-4 border-b border-gray-300 mb-4"}>
                                    <Link href="/public" className={"underline"}>
                                        {cartItems.user.name}
                                    </Link>
                                    <form action={route('cart.checkout')} method="post">
                                            <input type="hidden" name="_token" value={csrf_token} />
                                            <input type="hidden" name="vendor_id" value={cartItems.user.id} />
                                            <button className="btn btn-sm btn-ghost">
                                                <CreditCardIcon className={"size-6"} />
                                                Pay Only for this seller
                                            </button>
                                    </form>
                                </div>
                                {cartItems.items.map(item => (
                                    <>
                                     <CartItem item={item} key={item.id} />
                                    {/* {JSON.stringify(item, undefined, 2)} */}
                                    </>
                                ))}
                                </div>

                            ))}

                        </div>
                    </div>
                </div>
                <div className="card flex-1 bg-white dark:bg-gray-800 order-2 lg:order-1">
                    <div className="card-body">
                        Subtotal ({totalQuantity} items): &nbsp;
                        <CurrencyFormatter amount={totalPrice} />
                        <form action={route('cart.checkout')} method="post">
                            <input type="hidden" name="_token" value={csrf_token} />
                            <PrimaryButton className="rounded-full">
                                <CreditCardIcon className={"size-6"} />
                                Proceed to checkout
                            </PrimaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Index;
