<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <h1 class="text-center mb-4">
                    <i class="fas fa-book text-primary me-2"></i>
                    Vocabulary Time!
                </h1>

                <div class="text-center mb-4">
                    <h2 class="display-4 fw-bold text-primary">Hello</h2>

                    <button onclick="speakWord()" class="btn btn-custom mb-3">
                        <i class="fas fa-volume-up me-2"></i>Hear the word
                    </button>
                </div>

                <div class="text-center">
                    <h4 class="mb-3">What is the meaning?</h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer" id="option0" value="Olá">
                                <label class="form-check-label d-flex align-items-center p-3 border rounded w-100" for="option0">
                                    <span class="fw-bold me-2">A.</span> Olá
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer" id="option1" value="Tchau">
                                <label class="form-check-label d-flex align-items-center p-3 border rounded w-100" for="option1">
                                    <span class="fw-bold me-2">B.</span> Tchau
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer" id="option2" value="Obrigado">
                                <label class="form-check-label d-flex align-items-center p-3 border rounded w-100" for="option2">
                                    <span class="fw-bold me-2">C.</span> Obrigado
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answer" id="option3" value="Por favor">
                                <label class="form-check-label d-flex align-items-center p-3 border rounded w-100" for="option3">
                                    <span class="fw-bold me-2">D.</span> Por favor
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-custom btn-lg px-5">
                            <i class="fas fa-check me-2"></i>Submit Answer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
function speakWord() {
    let speech = new SpeechSynthesisUtterance("Hello");
    speech.lang = "en-US";
    window.speechSynthesis.speak(speech);
}
</script>
@endscript
