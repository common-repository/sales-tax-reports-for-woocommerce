<style>
    .custom-wc-report-container {
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 20px;
        padding-bottom: 50px;
        margin-bottom: 30px;
    }

    .custom-wc-report-table {
        border: 1px solid #ccc;
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    .custom-wc-report-table tr:nth-child(odd) {
        background-color: #f5f5f5;
    }

    .custom-wc-report-table th,
    .custom-wc-report-table td {
        border: 1px solid #ccc;
        padding: 5px;
        font-size: 11px;
        text-align: left;
    }

    .custom-wc-report-table th {
        background-color: #0073e6;
        color: #fff;
    }

    .custom-wc-report-container p {
        text-align: right;
        margin-top: 15px;
    }

    .button {
        background-color: #0073e6;
        color: #fff;
        padding: 10px 20px;
        text-decoration: none;
        display: inline-block;
        border-radius: 5px;
        margin-right: 10px;
    }

    .button:hover {
        background-color: #0055b3;
    }

    .dollar-amount:before {
        content:"$";
    }

    .download-sales-table, .download-summary-table {
        float: right;
        margin-left: 10px !important;
    }

    .sales-tax-form-filter input {
        margin-right: 10px;
        height: 40px;
    }

    .sales-tax-form-filter .state_select {
        margin-top: -3px ;
        margin-right: 10px;
    }

    .wc-state-tax-report-table-scroll {
        max-height: 70vh;
        overflow-y: scroll;
    }

    .wc-state-tax-report-summary-table {
        max-width: 500px;
        margin-bottom: 30px;
        float: right;
    }

    .wc-state-tax-report-summary-table td, .wc-state-tax-report-summary-table th {
        text-align: right;
    }

    .wc-state-tax-report-summary-table td:last-child, .wc-state-tax-report-summary-table th:last-child {
        max-width: 200px;
    }

    .clearfix {
        clear: both;
    }

    
    #sort-by-tax-rate:after {
        content: '↓';
        padding-left: 5px;
    }

    #sort-by-tax-rate.sorted-up:after {
        content: '↑' !important;
    }
    

</style>

<div class="custom-wc-report-container">
    <a href="#" id="sort-by-tax-rate" class="button">Sort by Tax Rate</a>
    <a href="#" class="button download-sales-table">Download Orders Table CSV</a>
    <a href="#" class="button download-summary-table">Download Summary Table CSV</a>
    <div class="wc-state-tax-report-table-scroll">
        <table class="custom-wc-report-table wc-state-tax-report-table">
            <tbody>
                <tr class="exportable">
                    <th>State</th>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Order Total</th>
                    <th>Shipping Charged</th>
                    <th>Shipping Tax</th>
                    <th>Tax Rate</th>
                    <th>Tax</th>
                    <th>SubTotal (no tax &amp; no ship)</th>
                </tr>
                <?php foreach ($rows as $row) : ?>
                    <tr class="export_table">
                        <td><?php echo $row['state']; ?></td>
                        <td><?php echo $row['order_id']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td class="dollar-amount"><?php echo $row['total']; ?></td>
                        <td class="dollar-amount"><?php echo $row['shipping']; ?></td>
                        <td class="dollar-amount"><?php echo $row['shipping_tax']; ?></td>
                        <td><?php echo $row['tax_rate']; ?></td>
                        <td class="dollar-amount"><?php echo $row['tax']; ?></td>
                        <td class="dollar-amount"><?php echo $row['cart_value']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>
    <table class="custom-wc-report-table wc-state-tax-report-summary-table">
        <tbody>
            <tr>
                <td colspan="6" style="text-align:left;">
                    <b>TOTALS</b>
                </td>
            </tr>
            <tr class="exportable">
                <th>Order Total</th>
                <th>Tax</th>
                <th>SubTotal (no tax &amp; no ship)</th>
            </tr>
            <tr>
                <td><?php echo wc_price($totalWithTaxShip); ?></td>
                <td><?php echo wc_price($totalTaxCollected); ?></td>
                <td><?php echo wc_price($totalNoTaxNoShip); ?></td>
            </tr>
        </tbody>
    </table>
    <div class="clearfix"></div>
    <?php if (count($tax_rate_array) > 0) : ?>
        <h3>Tax Rate Totals</h3>
        <table class="custom-wc-report-table">
            <tbody>
                <tr>
                    <th>Tax Rate Code</th>
                    <th>Tax Rate Total</th>
                    <th>District Sales Total (no tax & no ship)</th>
                </tr>
                <?php foreach ($tax_rate_array as $tax_code => $tax_rate) : ?>
                <tr class="export_summary">
                    <td><?php print preg_replace('/-/', ' ', $tax_code); ?></td>
                    <td class="dollar-amount"><?php print $tax_rate['rate_total']; ?></td>
                    <td class="dollar-amount"><?php print $tax_rate['sales_total']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="custom-wc-report-container">
    <?php
    // show some summaries
    echo "<h3>Total Sales</h3>";
    echo "Total Sales incl Tax/Ship: " . wc_price($totalWithTaxShip) . "</br>";
    echo "Total Sales NO  Tax/Ship: " . wc_price($totalNoTaxNoShip) . "</br>";
    ?>
</div>
