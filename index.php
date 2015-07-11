<?php
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET , POST");
            global $keyword;
            global $pageNo;
            if(isset($_GET['k_input']))
            {
                $keyword =$_GET['k_input'];
            }
            if(isset($_GET['pageNo'])){
            $pageNo = $_GET['pageNo'];
            }
            function buidOpSelector(){
                
            global $opsel;
            
                $opsel .="&outputSelector(0)=SellerInfo&outputSelector(1)=PictureURLSuperSize&outputSelector(2)=StoreInfo";
                return "$opsel";
            }
            function buildfilterArray ($filterarray) {
                            global $urlfilter;
                            global $i;
                                foreach($filterarray as $itemfilter) {
                                    foreach ($itemfilter as $key =>$value) {
                                            if(is_array($value)) {
                                                    foreach($value as $j => $content) { 
                                                    $urlfilter .= "&itemFilter($i).$key($j)=$content";
                                        }
                                    }
                                    else {
                                            if($value != "") {
                                                    $urlfilter .= "&itemFilter($i).$key=$value";
                                        }
                                    }
                                }
                                $i++;
                                }
                            return "$urlfilter";
                        } 
            //error_reporting(E_ALL);  // Turn on all errors, warnings and notices for easier debugging
            $endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
            $version = '1.0.0';  // API version supported by your application
            $appid = 'NAa9beda5-f82c-4a7a-a473-3dacf6c6cfe'; 
            $dataformat='XML';
            $query = isset($_GET["k_input"])?$_GET["k_input"]:'';  // You may want to supply your own query
            $safequery = urlencode($query);  // Make the query URL-friendly
            $pageEntry = isset($_GET['Page_Entry'])?$_GET['Page_Entry']:'';
            $sortOrder= isset($_GET['SORT'])?$_GET['SORT']:'';
            $i = '0'; 
            //create filter array
            $filterarray = array();
            $temp= array();
            if(isset($_GET['p1'])&&(!empty($_GET['p1'])||$_GET['p1'] == '0')){ array_push($filterarray,array(
                    'name' => 'MinPrice',
                    'value' => $_GET['p1'],
                    'paramName' => 'Currency',
                    'paramValue' => 'USD'));}
            if(isset($_GET['p2'])&&(!empty($_GET['p2'])||$_GET['p2'] == '0')){ array_push($filterarray,array(
                    'name' => 'MaxPrice',
                    'value' => $_GET['p2'],
                    'paramName' => 'Currency',
                    'paramValue' => 'USD'));}
            if(isset($_GET['c1'])|isset($_GET['c2'])|isset($_GET['c3'])|isset($_GET['c4'])|isset($_GET['c5'])){
                $val=array();
                if(isset($_GET['c1'])){array_push($val,$_GET['c1']);}
                if(isset($_GET['c2'])){array_push($val,$_GET['c2']);}
                if(isset($_GET['c3'])){array_push($val,$_GET['c3']);}
                if(isset($_GET['c4'])){array_push($val,$_GET['c4']);}
                if(isset($_GET['c5'])){array_push($val,$_GET['c5']);}
                array_push($filterarray,array(
                    'name' => 'Condition',
                    'value' => $val));
            }
            if(isset($_GET['f1'])|isset($_GET['f2'])|isset($_GET['f3'])){
                $val=array();
                if(isset($_GET['f1'])){array_push($val,$_GET['f1']);}
                if(isset($_GET['f2'])){array_push($val,$_GET['f2']);}
                if(isset($_GET['f3'])){array_push($val,$_GET['f3']);}
                array_push($filterarray, array('name'=>'ListingType',
                      'value'=>$val));
            }
            if(isset($_GET['s1'])){
               array_push($filterarray, array('name'=>'ReturnsAcceptedOnly',
                      'value'=>'true'));
            }
            if(isset($_GET['sh1'])){
               array_push($filterarray, array('name'=>'FreeShippingOnly',
                      'value'=>'true'));
            }
            if(isset($_GET['sh2'])){
                array_push($filterarray,array('name'=>'ExpeditedShippingType',
                      'value'=>$_GET['sh2']));
            }
            if(isset($_GET['sh3'])&&(!empty($_GET['sh3'])||$_GET['sh3'] == '0')){
                array_push($filterarray,array('name'=>'MaxHandlingTime',
                      'value'=>$_GET['sh3']));
            }
            $opsel=buidOpSelector();
            $urlfilter=buildfilterArray($filterarray) ;
            $apicall = "$endpoint?";
            $apicall .= "OPERATION-NAME=findItemsByKeywords";
            $apicall .= "&SERVICE-VERSION=$version";
            $apicall .= "&SECURITY-APPNAME=$appid";
            $apicall .= "&RESPONSE-DATAFORMAT=$dataformat";
            $apicall .= "&keywords=$safequery";
            $apicall .= "&paginationInput.entriesPerPage=$pageEntry";
            $apicall .= "&sortOrder=$sortOrder";
            $apicall .= "$urlfilter";
            $apicall .="$opsel";
            $apicall .="&paginationInput.pageNumber=$pageNo";
            //echo $urlfilter;
            //echo $apicall;
            $resp = simplexml_load_file($apicall);
            $count=$resp->paginationOutput->totalEntries;
            $itemCount=$resp->paginationOutput->entriesPerPage;
            $pageNo=$resp->paginationOutput->pageNumber;
            $jsonArray=array();
            $x=0;
            //for($p=1;$p<=$pageNo;$p++)
            if ($resp->ack == "Success" && $resp->searchResult['count'] != 0) {
                //echo $resp->ack;
                $jsonArray["ack"]= (string)$resp->ack;
                $jsonArray["resultCount"]=(string)$count;
                $jsonArray["pageNumber"]=(string)$pageNo;
                $jsonArray["itemCount"]=(string)$resp->searchResult['count'];
                foreach($resp->searchResult->item as $item){
                    $shippingLocations='';
                    $itemArray=array();
                    $basicInfo=array();
                    $sellerInfo=array();
                    $shipInfo=array();
                    $basicInfo["title"]=($item->title != null)?(string)$item->title:"NA";
                    $basicInfo["viewItemURL"]=($item->viewItemURL != null)?(string)$item->viewItemURL:"NA";
                    $basicInfo["galleryURL"]=($item->galleryURL != null)?(string)$item->galleryURL:"NA";
                    $basicInfo["pictureURLSuperSize"]=($item->pictureURLSuperSize != null)?(string)$item->pictureURLSuperSize:"NA";
                    $basicInfo["convertedCurrentPrice"]=($item->sellingStatus->convertedCurrentPrice != null)?(string)$item->sellingStatus->convertedCurrentPrice:"NA";
                    $basicInfo["shippingServiceCost"]=($item->shippingInfo->shippingServiceCost != null)?(string)$item->shippingInfo->shippingServiceCost:"NA";
                    $basicInfo["conditionDisplayName"]=($item->condition->conditionDisplayName != null)?(string)$item->condition->conditionDisplayName:"NA";
                    $basicInfo["listingType"]=($item->listingInfo->listingType != null)?(string)$item->listingInfo->listingType:"NA";
                    $basicInfo["location"]=($item->location != null)?(string)$item->location:"NA";
                    $basicInfo["categoryName"]=($item->primaryCategory->categoryName != null)?(string)$item->primaryCategory->categoryName:"NA";
                    $basicInfo["topRatedListing"]=($item->topRatedListing != null)?(string)$item->topRatedListing:"NA";
                    
                    $sellerInfo["sellerUserName"]=($item->sellerInfo->sellerUserName != null)?(string)$item->sellerInfo->sellerUserName:"NA";
                    $sellerInfo["feedbackScore"]=($item->sellerInfo->feedbackScore != null )?(string)$item->sellerInfo->feedbackScore:"NA";
                    $sellerInfo["positiveFeedbackPercent"]=($item->sellerInfo->positiveFeedbackPercent !=null)?(string)$item->sellerInfo->positiveFeedbackPercent:"NA";
                    $sellerInfo["feedbackRatingStar"]=($item->sellerInfo->feedbackRatingStar !=null)?(string)$item->sellerInfo->feedbackRatingStar:"NA";
                    $sellerInfo["topRatedSeller"]=($item->sellerInfo->topRatedSeller !=null)?(string)$item->sellerInfo->topRatedSeller:"NA";
                    $sellerInfo["sellerStoreName"]=($item->storeInfo->storeName !=null)?(string)$item->storeInfo->storeName:"NA";
                    $sellerInfo["sellerStoreURL"]=($item->storeInfo->storeURL !=null)?(string)$item->storeInfo->storeURL:"NA";
                    
                    foreach($item->shippingInfo->shipToLocations as $loc){
                        $shippingLocations .=(string)$loc;
                        $shippingLocations .=",";
                    }
                        
                    $shippingInfo["shippingType"]=($item->shippingInfo->shippingType !=null)?(string)$item->shippingInfo->shippingType:"NA";
                    $shippingInfo["shipToLocations"]=$shippingLocations;
                    $shippingInfo["expeditedShipping"]=($item->shippingInfo->expeditedShipping !=null)?(string)$item->shippingInfo->expeditedShipping:"NA";
                    $shippingInfo["oneDayShippingAvailable"]=($item->shippingInfo->oneDayShippingAvailable !=null)?(string)$item->shippingInfo->oneDayShippingAvailable:"NA";
                    $shippingInfo["returnsAccepted"]=($item->returnsAccepted !=null)?(string)$item->returnsAccepted:"NA";
                    $shippingInfo["handlingTime"]=($item->shippingInfo->handlingTime !=null)?(string)$item->shippingInfo->handlingTime:"NA";
                    
                    
                    $itemArray["basicinfo"]=$basicInfo;
                    $itemArray["sellerinfo"]=$sellerInfo;
                    $itemArray["shippinginfo"]=$shippingInfo;
                    
                    
                    $jsonArray["item$x"]=$itemArray;
                    $x=$x+1;
                }
            }
        else{
            $jsonArray["ack"]="No results found";
        }
        //print_r($jsonArray);
        //echo $apicall;
        if (isset($_GET['k_input'])){
        echo json_encode($jsonArray);
        }

        ?>
