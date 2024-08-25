<style>
.carousel-inner {
    position: relative;
    width: max-content;
    overflow: hidden;
    margin: auto;
}
.carousel-inner img {
    width: 600px;
    height: 100%;
}
</style>
<footer class="col-md-12 py-12" style="padding:0;background:#000;">
    <div id="demo" class="carousel slide" data-ride="carousel">

        <!-- Indicators -->
        <ul class="carousel-indicators">
            <li data-target="#demo" data-slide-to="0" class="active"></li>
            <li data-target="#demo" data-slide-to="1"></li>
            <li data-target="#demo" data-slide-to="2"></li>
            <li data-target="#demo" data-slide-to="3"></li>
            <li data-target="#demo" data-slide-to="4"></li>
            <li data-target="#demo" data-slide-to="5"></li>
            <li data-target="#demo" data-slide-to="6"></li>
            <li data-target="#demo" data-slide-to="7"></li>
        </ul>

        <!-- The slideshow -->
        <div class="carousel-inner">
            <div class="carousel-item active">
	            <img src="<?php echo base_url('assets/images/4. Republic day parade.jpg');?>" width="1100" height="500">
            </div>
            <div class="carousel-item">
	            <img src="<?php echo base_url('assets/images/1. Band Parade.jpg');?>" width="1100" height="500">
            </div>
            <div class="carousel-item">
	            <img src="<?php echo base_url('assets/images/3. Pentangular Meet.jpg');?>" width="1100" height="500">
            </div>
			<div class="carousel-item">
	            <img src="<?php echo base_url('assets/images/3.jpg');?>" width="1100" height="500">
            </div>
			<div class="carousel-item">
	            <img src="<?php echo base_url('assets/images/10.IH Basket Ball.jpg');?>" width="1100" height="500">
            </div>
			<div class="carousel-item">
	            <img src="<?php echo base_url('assets/images/10.jpg');?>" width="1100" height="500">
            </div>
			<div class="carousel-item">
	            <img src="<?php echo base_url('assets/images/13.Athletic Meet.jpg');?>" width="1100" height="500">
            </div>
			<div class="carousel-item">
	            <img src="<?php echo base_url('assets/images/16.Dinner Night.jpg');?>" width="1100" height="500">
            </div>
        </div>

        <!-- Left and right controls -->
        <a class="carousel-control-prev" href="#demo" data-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </a>
        <a class="carousel-control-next" href="#demo" data-slide="next">
            <span class="carousel-control-next-icon"></span>
        </a>
    </div>
</footer>

</body>
</html>