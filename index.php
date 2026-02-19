<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>E-Payment Integration Sandbox</title>
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                            <h4>Maklumat Pembayaran</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="01-mode-fpx.php" id="form-bayar">
                                <div class="mb-3 row">
                                    <label for="exchange_id" class="col-12 col-md-4 col-form-label">Exchange ID <span class="text-danger">*</span></label>
                                    <div class="col-12 col-md-8">
                                        <select class="form-control" name="EXCHANGE_ID" id="exchange_id">
                                            <?php
                                            $config = json_decode(file_get_contents('config.json'), true);
                                            foreach ($config['fpx']['exchange-id'] as $bank => $id) {
                                                echo "<option value=\"$id\">$bank - $id</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="merchant_code" class="col-12 col-md-4 col-form-label">Merchant Code <span class="text-danger">*</span></label>
                                    <div class="col-12 col-md-8">
                                        <select class="form-control" name="MERCHANT_CODE" id="merchant_code">
                                            <?php
                                            $config = json_decode(file_get_contents('config.json'), true);
                                            foreach ($config['fpx']['merchant-code'] as $bank => $code) {
                                                echo "<option value=\"$bank\">$bank - $code</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="merchant" class="col-12 col-md-4 col-form-label">Jabatan/Agensi/Syarikat <span class="text-danger">*</span></label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" class="form-control" name="merchant" value="Test agency">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="trans_id" class="col-12 col-md-4 col-form-label">ID Transaksi Pelanggan <span class="text-danger">*</span></label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" class="form-control" name="ORDER_ID" value="<?php echo uniqid('UAT_'); ?>">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="payee_name" class="col-12 col-md-4 col-form-label">Nama Pembayar <span class="text-danger">*</span></label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" class="form-control" name="CUSTOMER_NAME" value="">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="payee_email" class="col-12 col-md-4 col-form-label">Email Pembayar <span class="text-danger">*</span></label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" class="form-control" name="CUSTOMER_EMAIL" value="">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="payee_phone_no" class="col-12 col-md-4 col-form-label">No Telefon Pembayar <span class="text-danger">*</span></label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" class="form-control" name="CUSTOMER_MOBILE" value="">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="amount" class="col-12 col-md-4 col-form-label">Jumlah <span class="text-danger">*</span></label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" class="form-control" name="AMOUNT" value="">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="description" class="col-12 col-md-4 col-form-label">Keterangan <span class="text-danger">*</span></label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" class="form-control" name="TXN_DESC" value="">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="description" class="col-12 col-md-4 col-form-label">Return URL</label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" class="form-control" name="CALLBACK_URL" value="https://fpx.snapcash.com.my/response.php">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="description" class="col-12 col-md-4 col-form-label">Update URL</label>
                                    <div class="col-12 col-md-8">
                                        <input type="text" class="form-control" name="UPDATE_URL" value="https://fpx.snapcash.com.my/action.php?id=update">
                                    </div>
                                </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Pilih Bank</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end content -->
    </body>
</html>
