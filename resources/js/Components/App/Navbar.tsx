import { Link, usePage } from "@inertiajs/react";
import React from "react";
import MiniCartDropdown from "./MiniCartDropdown";

function Navbar() {
    const { url, props } = usePage(); // Correctly define usePage() inside the function
    const isDashboard = url.startsWith("/dashboard"); // Check if it's the dashboard page

    const { auth , totalPrice, $totalQuantity} = props;
    const {user} = auth; // Ensure user is defined properly

    return (
        <div className="navbar bg-base-100">
            <div className="flex-1">
                <Link href="/" className="btn btn-ghost text-xl">LaraStore</Link>
            </div>
            <div className="flex-none gap-3">
                {/* Cart Dropdown */}
              <MiniCartDropdown />

                {/* User Dropdown - Only on Dashboard */}
                {user && isDashboard && (
                    <div className="dropdown dropdown-end">
                        <div tabIndex={0} role="button" className="btn btn-ghost btn-circle avatar">
                            <div className="w-10 rounded-full">
                                <img
                                    alt="User Avatar"
                                    src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" />
                            </div>
                        </div>
                        <ul
                            tabIndex={0}
                            className="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-3 w-52 p-2 shadow">
                            <li>
                                <Link href={route("profile.edit")} className="justify-between">
                                    Profile
                                </Link>
                            </li>
                            <li><a>Settings</a></li>
                            <li>
                                <Link href={route("logout")} method="post" as="button">
                                    Logout
                                </Link>
                            </li>
                        </ul>
                    </div>
                )}

                {/* Show Login/Register if User is Not Logged In */}
                {!user && (
                    <>
                        <Link href={route("login")} className="btn">Login</Link>
                        <Link href={route("register")} className="btn btn-primary">Register</Link>
                    </>
                )}
            </div>
        </div>
    );
}

export default Navbar;
