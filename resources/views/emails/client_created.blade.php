<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Created</title>
</head>

<body class="bg-gray-100 font-sans">
    <div class="mx-auto mt-10 max-w-lg rounded-lg bg-white p-6 shadow-lg">
        <h1 class="mb-4 text-2xl font-bold text-gray-800">Hello, {{ $name }}</h1>
        <p class="mb-4 text-gray-600">
            Your account has been created successfully! Below are your login details:
        </p>
        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
            <ul class="space-y-2">
                <li class="flex items-center">
                    <span class="font-medium text-gray-700">Email:</span>
                    <span class="ml-2 text-gray-900">{{ $email }}</span>
                </li>
                <li class="flex items-center">
                    <span class="font-medium text-gray-700">Password:</span>
                    <span class="ml-2 text-gray-900">{{ $password }}</span>
                </li>
            </ul>
        </div>
        <p class="mb-4 text-gray-600">
            Please make sure to log in and change your password immediately for security purposes.
        </p>
        <div class="text-center">
            <a href="{{ config('app.url') }}"
                class="inline-block rounded-lg bg-blue-600 px-4 py-2 font-medium text-white shadow transition hover:bg-blue-700">
                Login to Your Account
            </a>
        </div>
        <p class="mt-6 text-sm text-gray-400">
            If you did not request this account, please ignore this email.
        </p>
    </div>
</body>

</html>
