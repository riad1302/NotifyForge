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
        <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>

        <?php if (isset($validation)): ?>
            <div class="text-red-500 mb-4 text-sm">
                <?= $validation->listErrors() ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/register" class="space-y-4">
            <?= csrf_field() ?>
            <input type="text" name="name" placeholder="Name" class="w-full p-2 border border-gray-300 rounded" required>
            <input type="email" name="email" placeholder="Email" class="w-full p-2 border border-gray-300 rounded" required>
            <input type="password" name="password" placeholder="Password" class="w-full p-2 border border-gray-300 rounded" required>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Register
            </button>
        </form>

        <p class="mt-4 text-sm text-center">
            Already have an account?
            <a href="/login" class="text-blue-600 hover:underline">Login</a>
        </p>
    </div>
</div>
</body>