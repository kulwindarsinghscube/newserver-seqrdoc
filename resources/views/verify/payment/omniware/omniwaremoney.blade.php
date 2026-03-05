<html>
<body>
<?php
      $salt = \Config::get('constant.omniware_salt'); //Pass your SALT here
      $_POST['api_key'] = \Config::get('constant.omniware_api_key'); //Pass your API KEY here
      $_POST['amount'] = $omniWareData['amount'];
      $_POST['city'] = 'vikhroli';
      $_POST['country'] = 'india';
      $_POST['currency'] = 'INR';
      $_POST['description'] = $omniWareData['product_info'];
      $_POST['email'] = $omniWareData['email'];
      $_POST['mode'] = 'TEST'; //'LIVE'
      $_POST['name'] = $omniWareData['name'];
      $_POST['order_id'] = $omniWareData['txnid'];
      $_POST['phone'] = $omniWareData['mobile_number'];
      $_POST['return_url'] = $omniWareData['return_url'];
      $_POST['zip_code'] = '400079';
      $hash = hashCalculate($salt, $_POST);

      function hashCalculate($salt,$input){
            /* Columns used for hash calculation, Donot add or remove values from $hash_columns array */
            $hash_columns = ['amount', 'api_key', 'city', 'country', 'currency', 'description', 'email', 'mode', 'name', 'order_id', 'phone', 'return_url', 'zip_code',];
            /*Sort the array before hashing*/
            sort($hash_columns);

            /*Create a | (pipe) separated string of all the $input values which are available in $hash_columns*/
            $hash_data = $salt;
            foreach ($hash_columns as $column) {
                  if (isset($input[$column])) {
                        if (strlen($input[$column]) > 0) {
                              $hash_data .= '|' . trim($input[$column]);
                        }
                  }
            }
            $hash = strtoupper(hash("sha512", $hash_data));
            
            return $hash;
      }

?>

<form action="https://pgbiz.omniware.in/v2/paymentrequest" method="post" name="omniwareForm" >
  <input type="hidden" value="<?php echo $hash;?>"                  name="hash"/>
  <input type="hidden" value="<?php echo $_POST['api_key'];?>"        name="api_key"/>
  <input type="hidden" value="<?php echo $_POST['return_url']; ?>"    name="return_url"/>
  <input type="hidden" value="<?php echo $_POST['mode'];?>"           name="mode"/>
  <input type="hidden" value="<?php echo $_POST['order_id'];?>"       name="order_id"/>
  <input type="hidden" value="<?php echo $_POST['amount'];?>"         name="amount"/>
  <input type="hidden" value="<?php echo $_POST['currency'];?>"       name="currency"/>
  <input type="hidden" value="<?php echo $_POST['description'];?>"    name="description"/>
  <input type="hidden" value="<?php echo $_POST['name'];?>"           name="name"/>
  <input type="hidden" value="<?php echo $_POST['email'];?>"          name="email"/>
  <input type="hidden" value="<?php echo $_POST['phone'];?>"          name="phone"/>
  <input type="hidden" value="<?php echo $_POST['city'];?>"           name="city"/>
  <input type="hidden" value="<?php echo $_POST['zip_code'];?>"       name="zip_code"/>
  <input type="hidden" value="<?php echo $_POST['country'];?>"        name="country"/>
  <!--<input type="submit" value="Submit"> -->
</form>


<script type="text/javascript">
  var omniwareForm = document.forms.omniwareForm;
  omniwareForm.submit();
</script>

</body>
</html>