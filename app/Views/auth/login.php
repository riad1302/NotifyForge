<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login/Register</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
</head>
<body>
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="text-red-500 mb-4 text-sm">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/login" class="space-y-4">
            <?= csrf_field() ?>
            <input type="email" name="email" placeholder="Email" class="w-full p-2 border border-gray-300 rounded" required>
            <input type="password" name="password" placeholder="Password" class="w-full p-2 border border-gray-300 rounded" required>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Login
            </button>
        </form>

        <p class="mt-4 text-sm text-center">
            Don't have an account?
            <a href="/register" class="text-blue-600 hover:underline">Register</a>
        </p>
    </div>
</div>
</body>