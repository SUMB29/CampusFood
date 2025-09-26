<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout Form</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#3de240ff',
          }
        }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">

  <div class="w-full max-w-lg bg-white shadow-lg rounded-2xl p-8">
    <h3 class="text-center text-2xl font-semibold mb-6 text-gray-800">
      Checkout Form
    </h3>

    <form action="payscript.php" method="post" class="space-y-4">

      <!-- Full Name -->
      <div>
        <label for="fname" class="block text-gray-700 font-medium mb-1">
          <i class="fa fa-user mr-2"></i> Full Name
        </label>
        <input type="text" id="fname" name="name" placeholder="John M. Doe" required
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:outline-none">
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-gray-700 font-medium mb-1">
          <i class="fa fa-envelope mr-2"></i> Email
        </label>
        <input type="email" id="email" name="email" placeholder="john@example.com" required
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:outline-none">
      </div>

      <!-- Hidden Inputs -->
      <input type="hidden" value="<?php echo 'OID'.rand(100,1000); ?>" name="orderid">
      <input type="hidden" value="<?php echo 1; ?>" name="amount">

      <!-- Mobile -->
      <div>
        <label for="mobile" class="block text-gray-700 font-medium mb-1">
          <i class="fa fa-mobile mr-2"></i> Mobile
        </label>
        <input type="tel" id="mobile" name="mobile" placeholder="+91 123 456 7890" required pattern="[0-9]{10,12}"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:outline-none">
      </div>

      <!-- Address -->
      <div>
        <label for="adr" class="block text-gray-700 font-medium mb-1">
          <i class="fa fa-address-card-o mr-2"></i> Address
        </label>
        <input type="text" id="adr" name="address" placeholder="542 W. 15th Street" required
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:outline-none">
      </div>

      <!-- Submit -->
      <div class="pt-4">
        <input type="submit" value="Proceed to Pay"
          class="w-full bg-primary text-white py-3 rounded-lg font-semibold shadow-md hover:bg-green-500 transition">
      </div>

    </form>
  </div>

</body>
</html>