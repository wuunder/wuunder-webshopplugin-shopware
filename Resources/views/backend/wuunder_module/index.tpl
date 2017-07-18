{extends file="parent:backend/_base/layout.tpl"}

{block name="content/main"}
    <button id="new_shipment" class="btn btn-primary">New shipment</button>
    <div id="main_content" hidden>
        <div class="page-header">
            <h2 id="shipments_header"></h2>
        </div>

        <div id="msg"</div>

        <pre id="code"></pre>

        <table id="table" class="table" hidden>
            <thead>
            <tr>
                <th>Nummer</th>
                <th>Info</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
{/block}

{block name="content/javascript"}
    <script>
        $(document).ready(function () {
            var order_id = localStorage.getItem('wuunder_order_id');

            $.ajax({
                method: 'POST',
                url: '/backend/wuunder_shipment/get_shipments',
                data: { order_id: order_id },
                success: function (data) {
                    $('#main_content').show();
                    $('#shipments_header').html('Shipments for order #' + data.order_nr);

                    if(data.shipments.length === 0){
                        $('#msg').html('No shipments.');
                    }
                    else {
                        $('#code').html(JSON.stringify(data.shipments, null, 2));
                    }
                }
            });

            $('#new_shipment').click(function (e) {
                $.ajax({
                    method: 'POST',
                    url: '/backend/wuunder_shipment/redirect',
                    data: { order_id: order_id },
                    success: function (data) {
                        window.open(data.redirect, "_blank");
                    }
                });
            });
        });
    </script>
{/block}