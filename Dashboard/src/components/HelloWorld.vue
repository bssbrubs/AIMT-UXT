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
    <img class='img' />
    </div>
  </div>
</template>

<script>
import simpleheat from 'simpleheat'
import mergeImages from 'merge-images'
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
    groupBy2 (arr, property) {
      return arr.reduce(function (memo, x) {
        if (!memo[x[property]]) { memo[x[property]] = [] }
        memo[x[property]].push(x)
        return memo
      }, {})
    },
    findImageData () {
      // var data = []
      // var lastRes = 0
      var lastScroll = 0
      var imagesByTime = []
      this.selectedTrace.sort((a, b) => (a.time > b.time) ? 1 : ((b.time > a.time) ? -1 : 0))
      this.selectedTrace.forEach(trace => { trace.time = Math.floor(trace.time) })
      var separateTime = this.groupBy2(this.selectedTrace, 'time')
      separateTime = Object.entries(separateTime)
      separateTime.forEach(time => {
        var array = []
        time[1].sort((a, b) => (a.scroll > b.scroll) ? 1 : ((b.scroll > a.scroll) ? -1 : 0))
        time[1].forEach(times => {
          var image = this.findImageURL(times.image)
          if (image !== '') {
            if (array.length === 0) {
              array.push({ src: image, x: 0, y: lastScroll })
            }
            if ((times.scroll - lastScroll) >= 600) {
              console.log(times.scroll)
              lastScroll = times.scroll
              array.push({ src: image, x: 0, y: lastScroll })
            }
          }
        })
        time[1].forEach(times => {
          var image = this.findImageURL(times.image)
          if (image !== '') {
            if (times.scroll >= lastScroll) {
              console.log(times.scroll)
              lastScroll = times.scroll
              array.push({ src: image, x: 0, y: lastScroll })
            }
          }
        })
        console.log(array)
        imagesByTime.push(array)
        lastScroll = 0
      })
      this.images_scroll = imagesByTime
      this.selected_img = imagesByTime[0]
      mergeImages(imagesByTime[0], { crossOrigin: 'Anonymous', height: 2613 })
        .then(b64 => { document.querySelector('img').src = b64 })
      // this.drawSimpleheat(data)
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
  width: 100%;
  position: absolute;
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
