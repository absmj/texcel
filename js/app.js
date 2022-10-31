const sprintf = (str, ...argv) =>
    !argv.length ? str : 
        sprintf(str = str.replace(sprintf.token||"%s", argv.shift()), ...argv);

const backend = '/app/index.php';

const [maxSize, minSize] = [Math.pow(8192, 2), Math.pow(50, 2)];

const az =  {
    width: `Eni`,
    height: `Uzun.`,
    text: `Mətn`,
    maxText: `Mətnin ölçüsü ilə formalaşacaq təsvirin sahəsi uyğun deyil. Bunlar arasında nisbət minimum 1 piksel uzunluğunda olmalıdır.`,
    size: `Ölçü`,
    settings: `Parametrlər`,
    info: `Ölçülər arasında proporsionallıq tələbi qoyulmayıb, lakin enin uzunluğu nisbəti ['1:1', '5:4', '4:3', '3:2', '8:5', '5:3', '16:9', '17:9'] ölçülərdən istifadə etmək məsləhət görülür`,
    aspectRatio: `Formalaşacaq təsvirin eninin uzunluğa nisbəti: `,
    maxSize: `Böyük ölçülərdə təsvirin formalaşması daha çox vaxt aparacağı üçün təsvirin maksimum sahəsi ${maxSize} piksel olmalıdır`,
    minSize: `Minimum təsvir sahəsi ${minSize} piksel miqdarı təyin edilmişdir`,
    minText: `Mətn daxil edilməyib`,
    generate: `Generasiya et`,
    filling: `Rəngləmə`,
    coverPhoto: `Şəkil ilə üzlənmə`,
    iterate: `İterasiya`,
    iteratePhoto: `Həmçinin şəkili də iterasiya et`,
    shape: `Fiqur`,
    shapeType: `Fiqurun tipi`,
    shapes: [`Kvadrat`, `Çevrə`],
    repeat: `Təkrarla`,
    repeatHelper: `Hər bir fiqurun optimal ölçünü müəyyən etmək mümkün olmadıqda, qalan sahənin piksellərinin təkrarlanması`,
    samples: `Nümunələr`,
    samples: [`Lorem Ipsum`],
    publications: `Nəşrlər`,
    wikipedia: `Vikipediya`,
    usersGuide: `İstifadəçi təlimatı`,
    download: `Yüklə`,
    loading: `Hazırlanır...`,
    rendered: `Şəkilin emalı tamamlanıb. Təsvir yüklənir...`,
    renderedInfo: `%s ədəd unikal rəng; Hər bir pikselin sahəsi: %sx%s`,
    file: `Fayl yüklənməyib`
};

const en = [
    `width`,
    `long.`,
    `Text`,
    `The area of ​​the image to be formed with the size of the text is not appropriate. The ratio between them must be at least 1 pixel long.`,
    `Size`,
    `Parameters`,
    `Proportionality is not required between dimensions, but width to length ratio ['1:1', '5:4', '4:3', '3:2', '8:5', '5:3', '16: 9', '17:9'] sizes are recommended`,
    `The ratio of the width to the length of the image to be formed:`,
    `The maximum area of ​​the image should be ${maxSize} pixels, as larger sizes will take longer to render`,
    `The minimum image area is set to ${minSize} pixels`,
    `No text included`,
    `Generate`,
    `Coloring`,
    `Face with picture`,
    `Iteration`,
    `Also iterate the image`,
    `Figure`,
    `Type of figure`,
    [`Rectangle`, `Circle`],
    `Repeat`,
    `If the optimal size of each figure cannot be determined, repeating the pixels of the remaining area`,
    `Examples`,
    [`Lorem Ipsum`],
    `Publications`,
    `User manual`,
    `Download`,
    `Preparing...`,
    `Image processing is complete. Loading image...`,
    `%s number of unique colors; Area of ​​each pixel: %sx%s`
];

const createLang = (source, lang) =>
                        Object.assign({}, ...Object.keys(source).map((key, index) => ({
                                [key]: lang[index]
                            })));

new Vue({
    el: "#app",
    data() {
        return {
            form: {},
            width: 1080,
            height: 1080,
            text: null,
            size: 1,
            image: {
                status: false,
                file: '',
                iterations: true
            },
            repeating: true,
            shape: 1,
            samples: 1,
            renderedImage: '',
            loading: false,
            info: 'info',
            server: 'loading',
            error: null,
            download: null,
            selectedLang: 'az',
            sizeSwitcher: 1,
            langs: {
                az,
                en: createLang(az, en),
                list(){
                    Object.values(this).filter(v => typeof v !== 'function')
                }
            },
            leftSide: false,
            rightSide: false
        }
    },

    computed: {
        inch()
        {
            return [
                this.width * (this.toPixel() * 0.0104166667),
                this.height * (this.toPixel() * 0.0104166667)
            ]
        },

        cm()
        {
            return [
                this.width * (this.toPixel() * 0.0264583333),
                this.height * (this.toPixel() * 0.0264583333)
            ]
        },

        pixel()
        {
            return [
                this.width * this.toPixel(),
                this.height * this.toPixel()
            ]
        },

        aspectRatio() 
        {
            const gcd = (a, b) => b ? gcd(b, a % b) : a;
            const common = gcd(this.width, this.height);
            return `${this.width / common}:${this.height / common}`;
        }
    },

    watch: {
        size(newValue, oldValue) {
            this.sizeSwitcher = oldValue;
            switch(newValue){
                case 2:
                    this.width = this.cm[0];
                    this.height = this.cm[1];
                    break;
                
                case 3:
                    this.width = this.inch[0];
                    this.height = this.inch[1];
                    break;
                
                default:
                    this.width = this.pixel[0];
                    this.height = this.pixel[1];
                    break;
            }
        },

        pixel(newValue, oldValue) {
            const area = newValue.reduce((a, b) => a * b) * this.toPixel();
            
            if(area > maxSize) return this.error = 'maxSize';

            if(area < minSize) return this.error = 'minSize';

            this.error = null;
        },

        text(value) {
            if(value.length == 0) return this.error = 'minText';

            this.error = null;
        },

        aspectRatio(value) {
            const regex = new RegExp(value, 'gs')
            
            if(!regex.test(value)) return this.info = 'info'; // :))
            
            this.info = 'aspectRatio';
        },

        image() {
            if(this.image.status) {
                if(this.image.file == '') return this.error = 'file';
            }
            this.error = null;
        },

        loading() {
            if(this.loading && this.error) {
                setTimeout(() => this.loading = false, 5000)
            }
        },

        deep: true
    },

    methods: {
        generateRequest(){
            const form = new FormData();
            this.image.file = $("#coverImage")?.[0]?.files[0];
            
            form.append("width", this.width);
            form.append("height", this.height);
            form.append("text", this.text);
            form.append("size", this.size);
            form.append("repeating", Number(this.repeating));
            form.append("shape", this.shape);
            form.append("image", Number(this.image.status))
            form.append("coverImage", this.image.file || '')
            form.append("iterations", Number(this.image.iterations))
            
            return form;
        },

        async generate()
        {
            if(this.error) return;
            
            this.loading = true;

            try{

                const request = await axios.post(backend, this.generateRequest())
                const img = new Image();
                
                this.renderedImage = `${backend}/image?rendered=${request.data.rendered}`;
                $("#result").find(".modal-title").text(
                    sprintf(this.langs[this.selectedLang].renderedInfo, 
                        request.data.pixelCount, request.data.xPixelSize, request.data.yPixelSize)
                );

                this.server = 'rendered';
                img.src = this.renderedImage;
                img.onload = () => {
                    this.server = 'loading';
                    this.loading = false;
                    $("#result").modal("show");
                }
            }
            catch(e){
                this.error = e.request.response ?? "Xəta baş verib! Ola bilsin, forma düzgün doldurulmayıb";
            }  
        },
        
        toPixel() 
        {
            switch(this.sizeSwitcher) {
                case 2:
                    return 37.7952755906;
                
                case 3:
                    return 96;
                
                default:
                    return 1;
            }       
        },

        async loadSample(name) {
            this.loading = true;
            try {
                const request = await axios.get(`/assets/samples/${name}`);
                this.text = request.data
                this.loading = false
                this.rightSide = false
            } catch (e) {
                this.error = e.message
            }
        }

    },

    mounted() {
    },

})