document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const selectedSubject = params.get("subject") || "Mathematics";

    const subjects = {
        Mathematics: {
            code: "MATH",
            type: "Core Subject",
        },
        Science: {
            code: "SCI",
            type: "Core Subject",
        },
        English: {
            code: "ENG",
            type: "Core Subject",
        },
        Filipino: {
            code: "FIL",
            type: "Core Subject",
        },
        "Araling Panlipunan": {
            code: "AP",
            type: "Core Subject",
        },
        TLE: {
            code: "TLE",
            type: "Core Subject",
        },
        MAPEH: {
            code: "MAPEH",
            type: "Core Subject",
        },
        "Values Education": {
            code: "VE",
            type: "Core Subject",
        },
    };

    const subject = subjects[selectedSubject] || subjects.Mathematics;

    const subjectName = document.getElementById("detailSubjectName");
    const infoName = document.getElementById("infoSubjectName");
    const infoCode = document.getElementById("infoSubjectCode");
    const infoType = document.getElementById("infoSubjectType");

    if (subjectName) {
        subjectName.textContent = selectedSubject;
    }

    if (infoName) {
        infoName.textContent = selectedSubject;
    }

    if (infoCode) {
        infoCode.textContent = subject.code;
    }

    if (infoType) {
        infoType.textContent = subject.type;
    }

    document.querySelectorAll(".class-assignment").forEach((link) => {
        const grade = link.dataset.grade;

        const destination = new URL("/admin/classes", window.location.origin);
        destination.searchParams.set("grade_level", grade);
        destination.searchParams.set("subject", selectedSubject);

        link.href = destination.pathname + destination.search;
    });
});
