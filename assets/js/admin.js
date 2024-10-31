(function ($) {
    $(document).ready(function () {

        $('.download-sales-table').click(function (e) {
            e.preventDefault();

            tableToCSV();
        });

        $('.download-summary-table').click(function (e) {
            e.preventDefault();

            summaryToCSV();
        });

        $('#sort-by-tax-rate').click(function (e) {
            e.preventDefault();
            if( ! $(this).hasClass('sorted-up') ) {
                $(this).addClass('sorted-up');
            }
            else {
                $(this).removeClass('sorted-up');
            }

            sortRowsByTaxRate('.wc-state-tax-report-table');
        });

    });
    
    var isSortedByTaxRate = false; // Add a variable to track the sorting state

    function sortRowsByTaxRate(tableSelector) {
        var $table = $(tableSelector);
        var rows = $table.find('tr.export_table').get(); // Include all rows, including header and total
        // Toggle the sorting order
        isSortedByTaxRate = !isSortedByTaxRate;
        rows.sort(function (a, b) {
            var taxRateA = $(a).find('td:nth-child(7)').text().trim();
            var taxRateB = $(b).find('td:nth-child(7)').text().trim();

            if (taxRateA === 'NONE (0%)') return 1; // Move "NONE (0%)" to the end
            if (taxRateB === 'NONE (0%)') return -1; // Move "NONE (0%)" to the end

            // Sort by tax rate, shorter first or reverse the order
            if (isSortedByTaxRate) {
                return taxRateA.localeCompare(taxRateB);
            } else {
                return taxRateB.localeCompare(taxRateA);
            }
        });
        // Remove all rows except the header and footer
        $table.find('tr.export_table:not(:first)').remove();
        // Append the sorted rows after the header
        for (var i = 0; i < rows.length; i++) {
            $table.append(rows[i]);
        }
    }

    function tableToCSV() {

        // Variable to store the final csv data
        var csv_data = [];

        // Get each row data
        var rows = $('tr.export_table');
        for (var i = 0; i < rows.length; i++) {

            // Get each column data
            var cols = rows[i].querySelectorAll('td,th');

            // Stores each csv row data
            var csvrow = [];
            for (var j = 0; j < cols.length; j++) {

                // Get the text data of each cell of
                // a row and push it to csvrow
                csvrow.push(cols[j].innerHTML);
            }

            // Combine each column value with comma
            csv_data.push(csvrow.join(","));
        }
        // combine each row data with new line character
        csv_data = csv_data.join('\n');

        downloadCSVFile(csv_data, 'SalesTaxReport');
    }

    function summaryToCSV() {

        // Variable to store the final csv data
        var csv_data = [];

        // Get each row data
        var rows = $('tr.export_summary');
        for (var i = 0; i < rows.length; i++) {

            // Get each column data
            var cols = rows[i].querySelectorAll('td,th');

            // Stores each csv row data
            var csvrow = [];
            for (var j = 0; j < cols.length; j++) {

                // Get the text data of each cell of
                // a row and push it to csvrow
                csvrow.push(cols[j].innerHTML);
            }

            // Combine each column value with comma
            csv_data.push(csvrow.join(","));
        }
        // combine each row data with new line character
        csv_data = csv_data.join('\n');

        downloadCSVFile(csv_data, 'SummaryTaxReport');
    }

    function downloadCSVFile(csv_data, file_name) {

        // Create CSV file object and feed our
        // csv_data into it
        CSVFile = new Blob([csv_data], { type: "text/csv" });

        // Create to temporary link to initiate
        // download process
        var temp_link = document.createElement('a');

        // Download csv file
        temp_link.download = file_name + "-" + Date.now() + ".csv";
        var url = window.URL.createObjectURL(CSVFile);
        temp_link.href = url;

        // This link should not be displayed
        temp_link.style.display = "none";
        document.body.appendChild(temp_link);

        // Automatically click the link to trigger download
        temp_link.click();
        document.body.removeChild(temp_link);
    }



}(jQuery));