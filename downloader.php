if ($what == 'product-images') {
            // Starting clock time in seconds
            $start_time = microtime(true);
            echo 'Starting product image transfer...';

            // turn on error reporting

            $api_path = "www.gqtobaccos.com/api/v2/products.xml?limit=250";
            $api_page_count = 38;
            // $api_path = "www.gqtobaccos.com/api/v2/products.xml?limit=10";
            // $api_page_count = 1;

            $xml_request_path = "https://" . $username . ":" . $token . "@" . $api_path;
            $xml_request_path_address = "https://" . $username . ":" . $token . "@www.gqtobaccos.com/api/v2";

            for ($x2 = 1; $x2 <= $api_page_count; $x2++) {
                $xml = simplexml_load_file($xml_request_path . "&page=" . $x2, 'SimpleXMLElement', LIBXML_NOCDATA);
                // Convert XML to array
                $xml_converted = json_decode(json_encode((array)$xml), TRUE);

                foreach ($xml_converted['product'] as $product) {

                    // Get the images for it
                    if (isset($product['images']['link']) && $product['images']['link'] != '') {
                        $product['images'] = json_decode(json_encode((array)simplexml_load_file($xml_request_path_address . $product['images']['link'])), TRUE)['image'];

                        // If there is more than 1 in the array
                        if (isset($product['images'][0])) {
                            foreach ($product['images'] as $image) {
                                $image_id = Media::downloadFromUrl($image['standard_url']);

                                if($image_id != false) {
                                    $productImageModel = new ProductImageModel();
                                    $productImageModel->where('product_id', $product['id'])->delete();

                                    $productImageModel->insert([
                                        'product_id' => $product['id'],
                                        'media_id' => $image_id
                                    ]);
                                }
                            }
                        } else {
                            $image_id = Media::downloadFromUrl($product['images']['standard_url']);

                            if($image_id != false) {
                                $productImageModel = new ProductImageModel();
                                $productImageModel->where('product_id', $product['id'])->delete();

                                $productImageModel->insert([
                                    'product_id' => $product['id'],
                                    'media_id' => $image_id
                                ]);
                            }
                        }
                    }
                }
            }

            $image_count = 101;

            // End clock time in seconds
            $end_time = microtime(true);
            $execution_time = ($end_time - $start_time);

            echo 'Product transfer completed in ' . $execution_time . ' seconds.';
            echo '</br>';
        }
