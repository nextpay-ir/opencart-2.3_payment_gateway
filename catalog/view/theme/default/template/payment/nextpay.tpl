<form action="<?php echo $action; ?>" method="post" id="payment">
<input type="hidden" name="trans_id" value="<?php echo $trans_id; ?>" />

<div class="buttons">
  <div class="pull-right">
    <input type="button" onclick="$('#payment').submit();" value="<?php echo $button_confirm; ?>" class="btn btn-primary" />
  </div>
</div>
</form>