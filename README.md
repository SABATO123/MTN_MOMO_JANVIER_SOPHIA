# MTN Mobile Money Simulation Project

This project simulates MTN Mobile Money transactions using the Africa's Talking API. It provides a platform to test and simulate mobile money transactions in a development environment.

## Prerequisites

Before you begin, ensure you have the following installed:
- PHP 7.4 or higher
- Composer (PHP package manager)
- XAMPP (or any other local server environment)
- Git

## Installation Steps

1. **Clone the Repository**
   ```bash
   git clone [your-repository-url]
   cd MTN_MOMO_JANVIER_SOPHIE
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure XAMPP**
   - Start Apache and MySQL services in XAMPP Control Panel
   - Place the project in the `htdocs` directory of your XAMPP installation
   - The default path should be: `C:\xampp\htdocs\MTN_MOMO_JANVIER_SOPHIE`

4. **Environment Setup**

   - Add your Africa's Talking API credentials:
     ```
     AFRICASTALKING_API_KEY=your_api_key
     AFRICASTALKING_USERNAME=your_username
     ```

## Running the Application

 **Start the Server**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services
   - The application will be available at: `http://localhost/MTN_MOMO_JANVIER_SOPHIE`


## Testing the Simulation

1. **Mobile Money Transactions**
   - Use the provided interface to simulate transactions
   - Test different transaction types:
     - Send Money
     - Withdraw Money
     - Check Balance
     - Transaction History

2. **API Testing**
   - Use Postman or any API testing tool to test the endpoints
   - Ensure your Africa's Talking API credentials are correctly configured






