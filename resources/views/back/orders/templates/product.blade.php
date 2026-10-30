<script id="product-template" type="template/html">

    <%
        const prices = Array.isArray(product.prices) ? product.prices : [];
        let priceItem = prices.find(item => String(item.id) === String(initialPriceId));

        if (priceItem == undefined) {
            priceItem = prices[0];
        }

        const salePrice = priceItem.sale_price ?? priceItem.price ?? 0;
        const regularPrice = priceItem.regular_price ?? salePrice;
    %>

    <div class="row order-single-product">
        <div class="col-md-1" style="padding-top: 33px;">
            <img class="w-100" src="<%= product.image %>" alt="<%= product.title %>">
        </div>
        <div class="col-md-3 mt-1">
            <div class="mb-1">
                <strong><%= product.title %></strong>
            </div>
            <% if (prices.length > 1) { %>
                <div class="mb-1">
                    <select class="form-control price-select">
                        <% prices.forEach(function(price, priceIndex){ %>
                            <%
                                const attributes = Array.isArray(price.attributes) ? price.attributes : [];
                                const attributeLabel = attributes.map(function(attribute) {
                                    const groupName = attribute && attribute.group && attribute.group.name ? attribute.group.name : '';
                                    const attributeName = attribute && attribute.name ? attribute.name : '';
                                    return groupName && attributeName ? `${groupName} : ${attributeName}` : attributeName;
                                }).filter(Boolean).join('، ');
                                let priceLabel = price.variant_name || attributeLabel || `واریانت #${price.id}`;

                                if (price.variant_code) {
                                    priceLabel += ` - کد: ${price.variant_code}`;
                                }

                                if (price.stock !== undefined && price.stock !== null && price.stock !== '') {
                                    priceLabel += ` - موجودی: ${price.stock}`;
                                }
                            %>
                            <option value="<%= price.id %>" data-price-index="<%= priceIndex %>" <% if (priceItem.id == price.id) { %> selected <% } %>>
                                <%= priceLabel %>
                            </option>
                        <% }); %>
                    </select>
                </div>
            <% } %>
            <strong class="text-success"><span class="sale-price"><%= number_format(salePrice) %></span> تومان</strong>

            <del class="text-danger regular-price-container <% if (!priceItem.discount) { %> d-none <% } %>"><span class="regular-price"><%= number_format(regularPrice) %></span> تومان</del>

            <input class="selected-price" name="products[<%= productsCount %>][price_id]" type="hidden" value="<%= priceItem.id %>">
            <input name="products[<%= productsCount %>][id]" type="hidden" value="<%= product.id %>">
        </div>
        <div class="col-md-2" style="padding-top: 18px;">
            <div class="form-group">
                <label for="">قیمت</label>
                <input type="number" name="products[<%= productsCount %>][price]" class="form-control product-price amount-input ltr" value="<%= regularPrice %>" readonly>
            </div>
        </div>
        <div class="col-md-2" style="padding-top: 18px;">
            <div class="form-group">
                <label for="">تخفیف (تومان)</label>
                <input type="number" name="products[<%= productsCount %>][discount]" class="form-control ltr product-discount amount-input" value="<%= regularPrice - salePrice %>" readonly>
            </div>
        </div>
        <div class="col-md-2" style="padding-top: 18px;">
            <div class="form-group">
                <label for="">تعداد</label>
                <input type="number" name="products[<%= productsCount %>][quantity]" class="form-control product-quantity ltr" <% if (priceItem.stock !== undefined && priceItem.stock !== null && priceItem.stock !== '') { %>max="<%= priceItem.stock %>"<% } %> min="1" value="1">
            </div>
        </div>
        <div class="col-md-2" style="padding-top: 38px;">
            <button type="button" class="btn btn-outline-danger delete-product-btn" style="margin-top: 8px;"><i class="feather icon-trash"></i></button>
        </div>
        <hr class="w-100">
    </div>
</script>
