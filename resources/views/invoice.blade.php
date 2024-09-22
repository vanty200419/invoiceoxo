<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="Invoice Estimate Management System" name="description">
    <meta name="csrf-token" content="{{csrf_token()}}">
    @php
        $settings = new App\MasterSetting();
        $site = $settings->siteData();
    @endphp
    <title>{{(isset($site['site_title']) && !empty($site['site_title']) ) ? $site['site_title'] : "InvoiOXO"}} | Invoice Bill </title>
    <link rel="icon" href="{{(isset($site['favicon']) && !empty($site['favicon']) && File::exists('uploads/favicon/'.$site['favicon'])) ? asset('uploads/favicon/'.$site['favicon']):asset('uploads/favicon/favicon.png')}}" type="image/x-icon"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{asset('assets/backend/plugins/fontawesome-free/css/all.min.css')}}">
    <script src="{{asset('assets/backend/plugins/jquery/jquery.min.js')}}"></script>
    <link rel="stylesheet" href="{{asset('assets/backend/dist/css/adminlte.min.css')}}">

    <body>
        <br>
        <br>
        <br>
    <div class="content p-3">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-body">
                            <div class="row pt-4">
                                <div class="col-lg-6 ">
                                    <p class="h5 font-weight-bold">{{ __('all.bill_to') }}</p>
                                    {{ $invoice->customer->display_name }}
                                    <address>
                                        {{ $invoice->customer->billing_address1 }}<br>
                                        {{ $invoice->customer->billing_state }}<br>
                                        {{ $invoice->customer->billing_zip }}<br>
                                        {{ $invoice->customer->phone }}
                                    </address>
                                </div>
                                <div class="col-lg-6 text-right">
                                    <h2 class="font-weight-bold">{{ __('all.invoice') }}</h2>
                                    <span>{{ __('all.invoice_no') }}:</span>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                    <br>
                                    <span>{{ __('all.invoice_date') }}:</span>
                                    <strong>{{ dateFormat($invoice->invoice_date) }}</strong>
                                </div>
                            </div>
                            @php
                                $settings = new App\MasterSetting();
                                $site = $settings->siteData();
                                $customers = App\Customer::latest()->get();
                                $products = App\Item::where('is_active', 1)->get();
                                $taxes = App\TaxType::where('is_active', 1)->get();
                                $currency = ($site['default_currency'] && $site['default_currency']) != '' ? $site['default_currency'] : '₹';
                            @endphp
                            <input type="hidden" value="{{ $invoice->id }}" name="invoiceId" id="invoiceId" />
                            <div class="table-responsive push">
                                <table class="table table-bordered table-hover text-nowrap" id="invoice-table">
                                    <tr class=" ">
                                        <th>{{ __('all.product') }}</th>
                                        <th class="text-center">{{ __('all.qty') }}</th>
                                        <th class="text-right">{{ __('all.unit_price') }}</th>
                                        <th class="text-right">{{ __('all.amount') }}</th>
                                    </tr>
                                </table>
                                <input type="hidden" name="tax_rate" id="tax_rate" />
                                <input type="hidden" name="tax_name" id="tax_name" />
                                <input type="hidden" name="tax_percentage" id="tax_percentage" />
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                </div>
                                <div class="col-sm-6 ">
                                    <div class="m-b-20">
                                        <div class="table-responsive no-border ">
                                            <table class="table mb-0 ">
                                                <tr>
                                                    <th>{{ __('all.sub_total') }}:{{ $currency }} </th>
                                                    <td class="text-right">{{ $invoice->sub_total }}</td>
                                                </tr>
                                                <tr>
                                                    <th>{{ __('all.tax') }}:{{ $currency }} <span
                                                            class="text-regular"></span></th>
                                                    <td class="text-right"> {{ $invoice->tax_amount }} </td>
                                                </tr>
                                                <tr>
                                                    <th>{{ __('all.total') }}:{{ $currency }} </th>
                                                    <td class="text-right text-primary">
                                                        <h5> {{ $invoice->total }} </h5>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <p class="text-muted text-center">{{ __('all.bill_footer_msg') }}</p>
                      
                        <div class="col-6 col-auto">
                       
                            <a href="{{ url('/customer/invoices/stripe-payment/'.$id.'/'.$email) }}" class="btn btn-primary float-right"><i class="fe fe-plus"></i>{{ __('all.pay_now') }}</a>
                     
                        </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>

<script src="{{asset('assets/backend/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/backend/dist/js/adminlte.js')}}"></script>
</html>

    <script>
        /* get invoice details */
        $(document).ready(function() {
            'use strict';
            var i = 0,
                payable = 0;
            /* to load already available invoice items */
            var invoiceId = $('#invoiceId').val();
            if (invoiceId != "") {
                /* if the invoice is non empty */
                var url = "{{ url('admin/invoices/get-invoice-product') }}" + '/' + invoiceId;
                var count = 0;
                $.get(url, function(data) {
                    $.each(data.data, function(key) {
                        var product_name = data.data[count].product_name;
                        var product_price = data.data[count].product_price;
                        var product_quantity = data.data[count].product_quantity;
                        var product_total = parseInt(data.data[count].product_quantity) *
                            parseFloat(data.data[count].product_price);
                        payable = payable + product_total;
                        var markup = '<tr id="row' + i +
                            '" class="no"> <td class="text-left"> <h6>' + product_name +
                            '<input type="hidden" name="name[]" value="' + product_name +
                            '" readonly/></h6></td> <td class="text-dark unit"> <span class="unit_qty"> ' +
                            product_quantity +
                            '</span> <input class="qty_product" type="hidden" name="qty[]" value="' +
                            product_quantity +
                            '" readonly/></td> <td class="text-dark qty"> <span class="unit_price">' +
                            product_price +
                            '</span> <input type="hidden" class="price_product unit" name="price[]" value="' +
                            product_price +
                            '" readonly/></td> <td class="total"> <span class="unit_total"> ' +
                            product_total +
                            '</span><input type="hidden" class="total_product" name="total[]" value="' +
                            product_total + '" readonly/></td></tr>';
                        $("table#invoice-table tbody").append(markup);
                        count = count + 1;
                        i = i + 1;
                    });
                })
            }
        });
    </script>