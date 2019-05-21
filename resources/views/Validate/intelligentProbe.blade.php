<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=emulateIE7" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/style.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/WdatePicker.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/skin_/table.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/skin_/index.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/jquery.grid.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/mystyle.css')}}" />

    <title>智能探针</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>姓名:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" id="id_name" value="" placeholder="必填"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>身份证:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 150px;height: 28px;" id="id_no" value="" placeholder="必填"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>手机号:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" id="phone" value="" placeholder="必填"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-button" style="margin-left: 20px;">
                        <input class="button" type="button" value="搜索一下" onclick="getProbe()"/>
                    </div>
                </div>
            <div class="table">
                <div class="grid display-none" id="probeTable"></div>
            </div>
            <div id="optable" class="optable" onclick="closeDetails()"></div>
            <div id="otable" class="otable">
                <div style="width: 100%;height: 30px;">
                    <ul>
                        <li><a href="javascript:void(0);">订单与资料信息</a></li>
                        <li><a href="javascript:void(0);">联系人与影像资料</a></li>
                        <li><a href="javascript:void(0);">通讯录</a></li>
                        <li><a href="javascript:void(0);">运营商报告</a></li>
                        <li><a href="javascript:void(0);">同盾报告查询</a></li>
                        <li><a href="javascript:void(0);">其他数据结果查询</a></li>
                    </ul>
                </div>
                <div class="utable" id="utable1"></div>
            </div>
        </div>
    </div>
</div>
</body>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/global.js"></script>
<script type="text/javascript" src="js/jquery.select.js"></script>
<script type="text/javascript" src="js/core.js"></script>
<script type="text/javascript" src="js/jquery.pagination.js"></script>
<script type="text/javascript" src="js/jquery.grid.js"></script>
<script type="text/javascript" src="js/WdatePicker.js"></script>
<script type="text/javascript" src="js/myjs.js"></script>
</html>
