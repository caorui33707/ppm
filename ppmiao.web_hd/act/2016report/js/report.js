$(function() {
    function a() {
        var a = $(".m-page01");
        $.loaded(),
        a.show(),
        a.fadeIn("1.5", "ease")
    }
    a();
    new Swiper(".swiper-container", {
        initialSlide: 0,
        direction: "vertical",
        onInit: function(a) {
            swiperAnimateCache(a),
            swiperAnimate(a)
        },
        onSlideChangeEnd: function(a) {
            function b(a, b, c) {
                c >= 10 ? (b.html(a[c]), clearInterval(e)) : b.html(a[c])
            }
            function c(a, b, c) {
                c >= 10 ? (b.html(a[c]), clearInterval(g)) : b.html(a[c])
            }
            if (swiperAnimate(a), 
			$(".swiper-slide .img_1").removeClass("lx"), 
			$(".h2_img1").removeClass("fandong1"), 
			$(".h2_img2").removeClass("fandong2"), 
			$(".h2_img3").removeClass("fandong3"), 
			$(".h2_img4").removeClass("fandong4"), 
			$(".h2_img5").removeClass("fandong5"), 
			$(".h2_img6").removeClass("fandong6"), 
			$(".h2_img7").removeClass("fandong7"), 
			$(".h2_img8").removeClass("fandong8"), 
			$(".h2_img9").removeClass("fandong9"), 
			$(".yb1").removeClass("yb1_dl"), 
			$(".yb2").removeClass("yb2_dl"), 
			$(".yb3").removeClass("yb3_dl"), 
			$(".yb4").removeClass("yb4_dl"), 
			$(".h2_w").removeClass("h2_wid"), 
			$("#sm").removeClass("sm"), 
			$("#cir").removeClass("cir"), 
			$(".h2_img").removeClass("fandong"), 
			0 == a.activeIndex && 
			$(".swiper-slide .img_1").addClass("lx"), 100 == a.activeIndex) {
                var d = 0,
                e = null;
                setTimeout(function() {
                    e = setInterval(function() {
                        d++,
                        b([" 11 ", " 18 ", " 08 ", " 22 ", " 26 ", " 09 ", " 15 ", " 23 ", " 21 ", " 18 ", " 28 "], $(".s02_1"), d),
                        b([" 17 ", " 34 ", " 08 ", " 56 ", " 33 ", " 28 ", " 15 ", " 16 ", " 10 ", " 24 ", " 45 "], $(".s02_2"), d),
                        b([" 26 ", " 19 ", " 29 ", " 15 ", " 18 ", " 45 ", " 33 ", " 26 ", " 16 ", " 15 ", " 37 "], $(".s02_3"), d)
                    },
                    100)
                },
                1200),
                $("#sm").addClass("sm"),
                $("#cir").addClass("cir")
            }
            if (3 == a.activeIndex) {
                var f = 0,
                g = null;
                $(".swiper-slide .img_1").addClass("lx"),
                $(".h2_w").addClass("h2_wid"),
                setTimeout(function() {
                    g = setInterval(function() {
                        f++,
                        c([" 11 ", " 18 ", " 08 ", " 22 ", " 26 ", " 09 ", " 15 ", " 23 ", " 21 ", " 33 ", " 29 ", " 33 "], $(".span02_1"), f),
                        c([" 3324 ", " 2668 ", " 1788 ", " 2356 ", " 3678 ", " 4288 ", " 2890 ", " 1567 ", " 3268 ", "3367", " 4918 ", " 3342 "], $(".span02_2"), f),
                        c([" 324 ", " 118 ", " 134 ", " 256 ", " 378 ", " 288 ", " 190 ", " 167 ", " 368 ", " 479 ", " 329 ", " 428 ", " 334 "], $(".span02_3"), f)
                    },
                    50)
                },
                2200)
            }
            2 == a.activeIndex && ($(".h2_img1").addClass("fandong1"), $(".h2_img2").addClass("fandong2"), $(".h2_img3").addClass("fandong3"), $(".h2_img4").addClass("fandong4"), $(".h2_img5").addClass("fandong5"), $(".h2_img6").addClass("fandong6"), $(".h2_img7").addClass("fandong7"), $(".h2_img8").addClass("fandong8"), $(".h2_img9").addClass("fandong9"), $(".yb1").addClass("yb1_dl"), $(".yb2").addClass("yb2_dl"), $(".yb3").addClass("yb3_dl"), $(".yb4").addClass("yb4_dl")),
            6 == a.activeIndex && $(".swiper-slide .img_1").addClass("lx"),
            9 == a.activeIndex && $(".swiper-slide .img_1").addClass("lx"),
            12 == a.activeIndex && $(".swiper-slide .img_1").addClass("lx"),
            15 == a.activeIndex && $(".swiper-slide .img_1").addClass("lx")
        }
    })
});