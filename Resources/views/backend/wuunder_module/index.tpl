{extends file="parent:backend/_base/layout.tpl"}

{block name="content/main"}
    <div id="main_content" hidden>
        <div class="page-header">
            <h2 id="shipments_header"></h2>
        </div>

        <div id="status"></div>

        <button id="new_shipment" class="btn btn-primary">New shipment</button>
        <button id="track_and_trace" class="btn btn-primary">Track & Trace</button>
    </div>
{/block}

{block name="content/javascript"}
    <script>
        $(document).ready(function () {
            var order_id = localStorage.getItem('wuunder_order_id');
            var main_content = '#main_content';
            var header = '#shipments_header';
            var status_bar = '#status';
            var new_shipment = '#new_shipment';
            var track_and_trace = '#track_and_trace';

            $(new_shipment).hide();
            $(track_and_trace).hide();

            function setStatus(status) {
                $(status_bar).html('<strong>Status: </strong>' + status);
            }

            function humanize(str) {
                var frags = str.split('_');
                for (var i = 0; i < frags.length; i++) {
                    frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
                }
                return frags.join(' ');
            }

            $.ajax({
                method: 'POST',
                url: '/backend/wuunder_shipment/get_shipments',
                data: { order_id: order_id },
                success: function (data) {
                    $(main_content).show();
                    $(header).html('Shipment for order #' + data.order_nr);

                    if(data.shipments.length === 0){
                        $(new_shipment).click(function (e) {
                            $.ajax({
                                method: 'POST',
                                url: '/backend/wuunder_shipment/redirect',
                                data: { order_id: order_id },
                                success: function (data) {
                                    window.open(data.redirect, "_blank");
                                }
                            });
                        });

                        $(new_shipment).show();
                        setStatus('Open')
                    }
                    else {
                        var shipment = data.shipments[0];
                        $(track_and_trace).click(function (e) {
                            window.open(shipment.track_and_trace_url, '_blank');
                        });
                        $(track_and_trace).show();
                        setStatus(humanize(shipment.status))
                    }
                }
            });
        });
    </script>
{/block}