<?php
$config_filename = 'config.json';
if (!file_exists($config_filename)) {
    throw new Exception("Can't find ".$config_filename);
}
$config = json_decode(file_get_contents($config_filename), true);
$data = $_POST;
$payload = NULL;
foreach ($data as $key => $val) {
    $payload .= "<input type='hidden' name='".$key."' value='".$val."'>";
}
$env = $config['fpx']['environment'];
$mode = $_POST['payment_mode'];

if(isset($_POST['EXCHANGE_ID'])){
    $exchange = $_POST['EXCHANGE_ID'];
} else {
    $exchange = $config['fpx']['exchange'];
}

if($mode == 'fpx'){
    $fpx = '01';
    $bank_type = 'Individu';
    $bank_description = 'Bagi pembayaran minimum RM 1.00 dan maksimum RM 30,000.00 (termasuk caj jika ada)';
} else {
    $fpx = '02';
    $bank_type = 'Korporat';
    $bank_description = 'Bagi pembayaran minimum RM 2.00 dan maksimum RM 1,000,000.00 (termasuk caj jika ada)';
}
?>
<!DOCTYPE HTML>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <title>E-Payment</title>
    <link rel="stylesheet" type="text/css" href="styles/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="styles/custom.css">
    <link
        href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900,900i|Source+Sans+Pro:300,300i,400,400i,600,600i,700,700i,900,900i&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="theme-light">
<section class="section d-flex justify-content-center align-items-center mt-3">
    <div class="row">
        <div class="cols">
            <div class="card">
            <div class="card-header">
                    <h3 class="text-center">Perbankan Internet (<?php echo $bank_type ?>)</h3>
                    <p class="text-center">Environment: <?php echo $config['fpx']['environment'] ?> Exchange ID: <?php echo $exchange ?> Merchant Code: <?php echo $_POST['MERCHANT_CODE'] ?></p>
                </div>
                <div class="content mb-2">
                    <p class="text-center"><?php echo $bank_description ?></p>
                    <div class="extraHeader">
                        <form class="search-form">
                            <div class="form-group searchbox">
                                <input type="text" class="form-control" placeholder="Cari..." id="filter">
                                <i class="fa-solid fa-magnifying-glass" style="left: 30px;position: fixed;"></i>
                            </div>
                        </form>
                    </div>
                    <div class="list-group list-custom-small" id="bank-list"></div>
                    <div class="d-grid gap-2 col-6 mx-auto mt-2">
                        <a href="#" onclick="history.back()" class="btn btn-danger">Kembali</a>
                    </div>
                </div>
                <div class="card-footer">
                    <p class="text-center">Hakcipta Terpelihara &copy; <?php echo date('Y') ?></p>
                    <p class="text-center"><img src="images/logo.png" title="logo" alt="logo" height="48px" class="img"></p>
                </div>
            </div>
            <form method="post" action="action.php?id=confirm-payment" id="form-bayar">
                <input type="hidden" id="bank-code" name="BANK_CODE" value="">
                <input type="hidden" id="bank-name" name="BANK_NAME" value="">
                <input type="hidden" id="be_message" name="BE_MESSAGE">
                <?php echo $payload ?>
            </form>
        </div>
    </div>
</section>
    <script type="text/javascript" src="scripts/bootstrap.min.js"></script>
    <script src="scripts/jquery.min.js"></script>
    <script>
        function get_list(){
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "php/bank-list.php",
                data:{
                    mode: '<?php echo $fpx ?>',
                    env: '<?php echo $env ?>',
                    exchange: '<?php echo $exchange ?>'
                },
                success: function(response) {
                    if(response.status == 'error'){
                        alert(response.message);
                        return false;
                    }
                    $.each(response.bank_list, function(key,value){
                        $('#bank-list').append('<a href="#" class="bank-code" data-bank-code="'+ key +'" data-bank-name="'+ value +'"><img src="images/bank/'+ key +'.png" height="48" title="'+ value +'" alt="'+ value +'"><span class="mx-3">'+ value +'</span><i class="fa fa-angle-right"></i></a>');
                    });
                    $('#be_message').val(response.be_message);
                    $('.bank-code').each(function() {
                        $(this).click(function(e) {
                            e.preventDefault();
                            let bank_code = $(this).data('bank-code');
                            let bank_name = $(this).data('bank-name');
                            $('#bank-code').val(bank_code);
                            $('#bank-name').val(bank_name);
                            $("#form-bayar").submit();
                        });
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    try {
                        var response = JSON.parse(jqXHR.responseText);
                        if(response.status == 'error'){
                            alert(response.message);
                        } else {
                            alert('Ralat: ' + textStatus + ' - ' + errorThrown);
                        }
                    } catch(e) {
                        alert('Ralat: ' + textStatus + ' - ' + errorThrown);
                    }
                }
            });
        }
        get_list();

        $("#filter").keyup(function() {

            // Retrieve the input field text and reset the count to zero
            var filter = $(this).val(),
            count = 0;

            // Loop through the comment list
            $('#bank-list a').each(function() {


                // If the list item does not contain the text phrase fade it out
                if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).hide();

                // Show the list item if the phrase matches and increase the count by 1
                } else {
                $(this).show();
                count++;
                }

            });

        });

        </script>
</body>
</html>