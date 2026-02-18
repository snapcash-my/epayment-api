<?php
$content_type = $_SERVER["CONTENT_TYPE"];

if(!$content_type){
    http_response_code(400); // Bad Request
    echo "Wrong content type";
}

$config_filename = 'config.json';

if (!file_exists($config_filename)) {
    throw new Exception("Can't find ".$config_filename);
}

$config = json_decode(file_get_contents($config_filename), true);
$payload = NULL;

if($content_type == 'application/x-www-form-urlencoded'){
    $data = $_POST;
    if ($data !== null) {
        foreach ($data as $key => $val) {
            $payload .= "<input type='hidden' name='".$key."' value='".$val."'>";
        }
    } else {
        http_response_code(400); // Bad Request
        echo "Invalid post data";
    }
}

if($content_type == 'application/json'){
    $data = file_get_contents('php://input');
    if ($data !== null) {
        foreach (json_decode($data) as $key => $val) {
            $payload .= "<input type='hidden' name='".$key."' value='".$val."'>";
        }
    } else {
        http_response_code(400); // Bad Request
        echo "Invalid JSON data";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>E-Payment Integration Sandbox</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta content="" name="description" />
        <meta content="Fadli Saad" name="author" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="images/favicon.png">

        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="styles/bootstrap.css" type="text/css">
    </head>

    <body>
        <!-- content start -->
        <div class="container mt-3">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="card border">
                        <div class="card-header">
                            <h4>Cara Pembayaran</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-center">Pilih Perbankan Internet (Individu/Korporat)<br>atau<br>Kad Kredit/Debit</p>
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action payment-mode d-flex align-items-center" data-payment-mode="fpx" id="fpx">
                                    <img src="images/fpx.svg" height="48" title="Personal Banking" alt="Personal Banking">
                                    <span class="mx-3">Perbankan Internet (Individu)</span>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action payment-mode d-flex align-items-center" data-payment-mode="fpx1" id="fpx1">
                                    <img src="images/fpx.svg" height="48" title="Corporate Banking" alt="Corporate Banking">
                                    <span class="mx-3">Perbankan Internet (Korporat)</span>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action payment-mode d-flex align-items-center" data-payment-mode="migs" id="migs">
                                    <img src="images/visa.svg" height="48" title="Credit/Debit Card" alt="Credit/Debit Card">
                                    <img src="images/mastercard.svg" height="48" title="Credit/Debit Card" alt="Credit/Debit Card">
                                    <span class="mx-3">Kad Kredit/Debit</span>
                                </a>
                            </div>
                            <dl class="mt-3">
                                <dt>Perbankan Individu</dt>
                                <dd>Minimum RM 1.00 dan maksimum RM 30,000.00</dd>
                                <dt>Perbankan Korporat</dt>
                                <dd>Minimum RM 2.00 dan maksimum RM 1,000,000.00</dd>
                                <dt>Kad Kredit/Debit</dt>
                                <dd>Tertakluk kepada had kad anda</dd>
                            </dl>
                            <div class="d-grid gap-2 col-8 col-md-6 mx-auto mt-3">
                                <a href="#" onclick="cancel()" class="btn btn-danger">Batal</a>
                            </div>
                        </div>
                        <div class="card-footer">
                            <p class="text-center mb-1">Hakcipta Terpelihara &copy; <?php echo date('Y') ?></p>
                            <p class="text-center mb-0"><img src="images/logo.png" title="logo" alt="logo" height="48px" class="img"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end content -->
        <form method="post" action="action.php?id=choose-bank" id="form-bayar">
            <input type="hidden" id="payment-mode" name="payment_mode" value="">
            <?php echo $payload ?>
        </form>
        <script type="text/javascript" src="scripts/bootstrap.min.js"></script>
        <script src="scripts/jquery.min.js"></script>
        <script>
            $('.payment-mode').each(function() {
                $(this).click(function() {
                    let amount = '<?php echo $data['amount'] ?? '100' ?>';
                    let payment_mode = $(this).data('payment-mode');

                    if(payment_mode == 'fpx'){
                        let minAmount = 1;
                        let maxAmount = 30000;
                        if(minAmount <= amount <= maxAmount){
                            $('#fpx').click(function(e){
                                e.preventDefault();
                            });
                        }
                    }
                    if(payment_mode == 'fpx1'){
                        let minAmount = 2;
                        let maxAmount = 1000000;
                    }
                    $('#payment-mode').val(payment_mode);
                    $("#form-bayar").submit();
                });
            });

            function cancel() {
                document.getElementById("form-bayar").action = 'action.php?id=cancel-payment';
                document.getElementById("form-bayar").submit();
            }
        </script>
    </body>
</html>
