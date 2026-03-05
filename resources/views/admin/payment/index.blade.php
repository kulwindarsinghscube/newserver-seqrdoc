
<div class="container">
   
    <form action="{{ route('payment.request') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Amount</label>
            <input type="text" name="amount" class="form-control" value="1.00">
        </div>
        <div class="mb-3">
            <label>Billing Name</label>
            <input type="text" name="billing_name" class="form-control" value="Test User">
        </div>
        <div class="mb-3">
            <label>Billing Phone</label>
            <input type="text" name="billing_tel" class="form-control" value="9876543210">
        </div>
        <div class="mb-3">
            <label>Billing Email</label>
            <input type="email" name="billing_email" class="form-control" value="test@test.com">
        </div>
        <button type="submit" class="btn btn-primary">Pay Now</button>
    </form>
</div>

