<template>
  <div>
    <br />
    <v-row>
      <v-col class="d-flex" cols="12" sm="6">
        <v-select
          v-model="sample_selected"
          :items="samples"
          @change="changeSelectedSample()"
          filled
          label="Filled style"
          dense
          item-value="sample_name"
          item-text="sample_name"
        ></v-select>
      </v-col>
    </v-row>
    <v-btn color="green" fab large dark bottom left class="v-btn--example" @click="previousImage()">
      <v-icon>mdi-chevron-double-left</v-icon>
    </v-btn>
    <v-btn color="green" fab large dark bottom left class="v-btn--example2" @click="nextImage()">
      <v-icon>mdi-chevron-double-right</v-icon>
    </v-btn>
    <div id="container" style="width: 1366px; height: 2596px;
    position: absolute;">
    <ul id="v-for-object" class="demo">
    <li v-for="item in selected_img" :key="item">
      <v-row v-bind:style="{ 'margin-top': item.scroll + 'px', 'z-index': globalIndex - 1 }">>
          <img class='ibagem' :src="item.image" />
      </v-row>
    </li>
    </ul>
      <canvas
        width="1366"
        height="2596"
        style="width: 1366px; height: 2596px; position: absolute; opacity: 75%;"
        id="plotter"
      ></canvas>
    </div>
  </div>
</template>

<script>
import simpleheat from 'simpleheat'

import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'HelloWorld',
  props: {
    msg: String
  },
  data: () => ({
    heatmap: {
      max: 1,
      canvas: '',
      width: 0,
      height: 0,
      data: []
    },
    ctx: 0,
    globalIndex: 1000000000000000,
    samples_url: [],
    sample_selected: '',
    selected_path: [],
    image_in_view: {},
    image_pos: 0,
    selectedObj: [],
    images_scroll: [],
    selected_img: []
  }),
  components: {},
  computed: {
    ...mapGetters({
      samples: 'samples/getSample',
      images: 'samples/getImages',
      selectedTrace: 'samples/getSpecSample'
    })
  },
  mounted () {
    this.fetchSamples()
  },
  methods: {
    ...mapActions({
      fetchSamples: 'samples/fetchSample',
      fetchSampleData: 'samples/fetchSpecificSample',
      fetchImages: 'samples/fetchImages'
    }),
    changeSelectedSample () {
      this.samples.forEach(sample => {
        if (this.sample_selected === sample.sample_name) {
          this.selectedObj = sample
          var localPath = sample.url_path
          var realPath = sample.real_path
          this.selected_path = ({ localPath, realPath })
        }
      })
      this.findImages()
      this.findTraceData()
    },
    findImages () {
      this.fetchImages(this.selected_path).then(response => {
        this.image_in_view = this.images[2]
      })
    },
    findTraceData () {
      this.fetchSampleData(this.selectedObj.real_path).then(response => {
        this.findImageData()
      })
    },
    findImageData () {
      var data = []
      var lastRes = 0
      var lastScroll = 0
      var imagesByTime = []
      this.selectedTrace.forEach(trace => {
        if (trace.time >= lastRes) {
          if (trace.scroll > lastScroll) {
            if (lastRes + 2 >= Math.floor(trace.time) + 2) { lastRes = Math.floor(trace.time) + 2 }
            imagesByTime.push(trace)
          }
          lastScroll = 0
        }
      })
      imagesByTime.splice(0, 1)
      console.log(imagesByTime)
      var aux = -1
      var lastImage = ''
      for (let index = 0; index < imagesByTime.length; index++) {
        var array = []
        imagesByTime[index].forEach(time => {
          if (time.scroll > aux) {
            var background = new Image()
            background.src = this.image_in_view.path + '?' + new Date().getTime()
            aux = time.scroll
            var scroll = time.scroll
            var image = this.findImageURL(time.image)
            if (lastImage !== image) {
              lastImage = image
              array.push({ image, scroll })
            }
          }
        })
        this.images_scroll.push(array)
      }

      console.log(this.images_scroll)

      this.selected_img = this.images_scroll[1]

      // console.log(this.images_scroll)

      // this.selectedTrace.forEach(trace => {
      //   if (this.image_in_view.image === trace.image) {
      //     data.push([trace.X, trace.Y])
      //     if (trace.scroll >= lastRes) {
      //       var background = new Image()
      //       background.src = this.image_in_view.path + '?' + new Date().getTime()
      //       lastRes = trace.scroll + background.height * 0.3
      //       var scroll = trace.scroll
      //       var image = trace.image
      //       this.images_scroll.push({ image, scroll })
      //     }
      //   }
      // })
      // console.log(this.images_scroll)
      // this.takeBack()
      // this.foundScrollImages(this.selectedTrace)
      this.drawSimpleheat(data)
    },
    drawSimpleheat (data) {
      this.canvas = document.getElementById('plotter')
      const heat = simpleheat(this.canvas).data(data)
      // var ctx = this.canvas.getContext('2d')
      this.heatmap.width = this.canvas.width
      this.heatmap.height = this.canvas.height

      // var background = new Image()
      // background.src = this.image_in_view.path + '?' + new Date().getTime()
      // background.setAttribute('crossOrigin', '')

      // // Make sure the image is loaded first otherwise nothing will draw.
      // background.onload = function () {
      //   ctx.drawImage(background, 0, 0)
      // }

      heat.max(this.heatmap.max)
      heat.data(data)
      heat.draw()
      heat.radius(10, 30)
    },
    findImageURL (image) {
      var obj = ''
      this.images.forEach(img => {
        if (img.image === image) {
          obj = img.path
        }
      })
      return obj
    },
    previousImage () {
      if (this.image_pos >= 1) {
        this.image_pos -= 1
        this.image_in_view = this.images[this.image_pos]
        this.selected_img = this.images_scroll[this.image_pos]
        // this.findImageData()
      }
    },
    nextImage () {
      this.image_pos += 1
      this.image_in_view = this.images[this.image_pos]
      this.selected_img = this.images_scroll[this.image_pos]
      // this.findImageData()
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
#lateral .v-btn--example {
  bottom: 0;
  position: absolute;
  margin: 0 0 16px 16px;
}

#lateral .v-btn--example2 {
  bottom: 0;
  position: absolute;
  margin: 16px 0 0 0;
}

.ibagem {
  width: 100%; position: absolute;
      left:0px;
}

/* body{text-align: center;background: #f2f6f8;}
.img{position:absolute;z-index:1;}

#container{
    display:inline-block;
    width:320px;
    height:480px;
    margin: 0 auto;
    background: black;
    position:relative;
    border:5px solid black;
    border-radius: 10px;
    box-shadow: 0 5px 50px #333}

#plotter{
    position:relative;
    z-index:20;
} */
</style>
